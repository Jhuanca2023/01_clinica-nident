/**
 * Producto Live Search (pls-) — autocomplete tipo Capre/Google.
 * Dropdown absoluto bajo el input del header Liam.
 */
(function (window, document) {
  "use strict";

  var ROOT_ID = "pls-search-results";

  function debounce(fn, wait) {
    var t;
    return function () {
      var ctx = this;
      var args = arguments;
      clearTimeout(t);
      t = setTimeout(function () {
        fn.apply(ctx, args);
      }, wait);
    };
  }

  function money(n) {
    var v = Number(n);
    if (!isFinite(v)) return "—";
    return "S/ " + v.toFixed(2);
  }

  function escapeHtml(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function ProductoLiveSearchWidget(opts) {
    opts = opts || {};
    this.input =
      typeof opts.input === "string"
        ? document.querySelector(opts.input)
        : opts.input;
    this.form =
      typeof opts.form === "string"
        ? document.querySelector(opts.form)
        : opts.form;
    this.trigger =
      typeof opts.trigger === "string"
        ? document.querySelector(opts.trigger)
        : opts.trigger;
    this.endpoint = opts.endpoint || "/web_buscar_productos";
    this.resultsUrl = opts.resultsUrl || "/tienda";
    this.limit = opts.limit || 8;
    this.minChars = opts.minChars || 2;
    this.debounceMs = opts.debounceMs || 280;
    this.dropdown = null;
    this.activeIndex = -1;
    this.lastPayload = null;
    this.abortCtrl = null;
    this._open = false;
    this._reqId = 0;

    if (!this.input) {
      console.warn("[pls] No se encontró #lv-search-input");
      return;
    }

    this.mount();
  }

  ProductoLiveSearchWidget.prototype.mount = function () {
    var self = this;
    var host =
      this.input.closest(".lv-header__search") ||
      this.input.closest(".lv-header__search-form") ||
      this.input.parentElement;

    if (host && window.getComputedStyle(host).position === "static") {
      host.style.position = "relative";
    }
    if (host) {
      host.classList.add("pls-ready");
      host.style.overflow = "visible";
    }

    var existing = document.getElementById(ROOT_ID);
    if (existing) existing.remove();

    this.dropdown = document.createElement("div");
    this.dropdown.id = ROOT_ID;
    this.dropdown.className = "pls-dropdown";
    this.dropdown.setAttribute("role", "listbox");
    this.dropdown.setAttribute("aria-live", "polite");
    this.dropdown.hidden = true;
    this.dropdown.innerHTML =
      '<div class="pls-dropdown__scroll" data-pls-scroll></div>' +
      '<div class="pls-suggestions" data-pls-suggestions hidden></div>' +
      '<div class="pls-footer" data-pls-footer hidden></div>';

    // Siempre en body + fixed: el header tiene overflow:hidden / height:40px
    // y recorta cualquier dropdown hijo absoluto.
    document.body.appendChild(this.dropdown);
    this._anchorMode = "fixed";
    this._host = host;

    this.input.setAttribute("autocomplete", "off");
    this.input.setAttribute("autocorrect", "off");
    this.input.setAttribute("spellcheck", "false");
    this.input.setAttribute("role", "combobox");
    this.input.setAttribute("aria-autocomplete", "list");
    this.input.setAttribute("aria-controls", ROOT_ID);

    var onType = debounce(function () {
      self.fetchSuggest(self.input.value);
    }, this.debounceMs);

    this.input.addEventListener("input", onType);
    this.input.addEventListener("keyup", function (e) {
      if (e.key === "Escape" || e.key === "ArrowDown" || e.key === "ArrowUp" || e.key === "Enter") {
        return;
      }
      onType();
    });
    this.input.addEventListener("focus", function () {
      self.positionDropdown();
      var q = (self.input.value || "").trim();
      if (q.length >= self.minChars) {
        if (self.lastPayload) {
          self.render(self.lastPayload);
          self.open();
        } else {
          self.fetchSuggest(q);
        }
      }
    });
    this.input.addEventListener("keydown", function (e) {
      self.onKey(e);
    });

    if (this.form) {
      this.form.addEventListener(
        "submit",
        function (e) {
          var q = (self.input.value || "").trim();
          if (q.length < self.minChars) {
            e.preventDefault();
            self.input.focus();
            return;
          }
          e.preventDefault();
          self.close();
          self.goToResults(q);
        },
        true
      );
    }

    if (this.trigger) {
      this.trigger.addEventListener(
        "click",
        function (e) {
          e.preventDefault();
          e.stopPropagation();
          var q = (self.input.value || "").trim();
          if (q.length >= self.minChars) {
            self.close();
            self.goToResults(q);
          } else {
            self.input.focus();
          }
        },
        true
      );
    }

    document.addEventListener("click", function (e) {
      if (
        self.input.contains(e.target) ||
        (self.trigger && self.trigger.contains(e.target)) ||
        (self.dropdown && self.dropdown.contains(e.target))
      ) {
        return;
      }
      self.close();
    });

    window.addEventListener(
      "resize",
      function () {
        if (self._open) self.positionDropdown();
      },
      { passive: true }
    );
    window.addEventListener(
      "scroll",
      function () {
        if (self._open) self.positionDropdown();
      },
      true
    );

    // Si el usuario ya tenía texto al cargar (p.ej. desde /tienda?q=)
    var initial = (this.input.value || "").trim();
    if (initial.length >= this.minChars && document.activeElement === this.input) {
      this.fetchSuggest(initial);
    }
  };

  ProductoLiveSearchWidget.prototype.positionDropdown = function () {
    if (!this.dropdown || !this.input) return;

    if (this._anchorMode === "local") {
      this.dropdown.style.position = "absolute";
      this.dropdown.style.left = "0";
      this.dropdown.style.right = "0";
      this.dropdown.style.top = "calc(100% + 6px)";
      this.dropdown.style.width = "auto";
      this.dropdown.style.minWidth = "100%";
      this.dropdown.style.zIndex = "99999";
      return;
    }

    var anchor =
      this.input.closest(".lv-header__search") ||
      this.input.closest(".lv-header__search-form") ||
      this.input;
    var a = anchor.getBoundingClientRect();
    var left = a.left;
    var width = Math.max(a.width, 280);
    var top = a.bottom + 6;
    var maxW = Math.min(420, window.innerWidth - 16);
    if (width > maxW) width = maxW;
    if (left + width > window.innerWidth - 8) {
      left = Math.max(8, window.innerWidth - width - 8);
    }
    this.dropdown.style.position = "fixed";
    this.dropdown.style.left = left + "px";
    this.dropdown.style.top = top + "px";
    this.dropdown.style.width = width + "px";
    this.dropdown.style.right = "auto";
    this.dropdown.style.zIndex = "99999";
  };

  ProductoLiveSearchWidget.prototype.goToResults = function (q) {
    var base = this.resultsUrl || "/tienda";
    var sep = base.indexOf("?") >= 0 ? "&" : "?";
    window.location.href = base + sep + "q=" + encodeURIComponent(q);
  };

  ProductoLiveSearchWidget.prototype.fetchSuggest = function (raw) {
    var q = String(raw || "").trim();
    var self = this;

    if (q.length < this.minChars) {
      this.close();
      this.lastPayload = null;
      return;
    }

    if (this.abortCtrl) {
      try {
        this.abortCtrl.abort();
      } catch (e) {}
    }
    this.abortCtrl =
      typeof AbortController !== "undefined" ? new AbortController() : null;
    var reqId = ++this._reqId;

    this.renderLoading();
    this.positionDropdown();
    this.open();

    var url =
      this.endpoint +
      (this.endpoint.indexOf("?") >= 0 ? "&" : "?") +
      "query=" +
      encodeURIComponent(q) +
      "&limit=" +
      encodeURIComponent(this.limit);

    var fetchOpts = {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      credentials: "same-origin",
    };
    if (this.abortCtrl) fetchOpts.signal = this.abortCtrl.signal;

    fetch(url, fetchOpts)
      .then(function (r) {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.json();
      })
      .then(function (data) {
        if (reqId !== self._reqId) return;
        if (Array.isArray(data)) {
          data = { success: true, query: q, products: data, suggestions: [] };
        }
        if (!data || typeof data !== "object") {
          data = { success: false, products: [], suggestions: [] };
        }
        if (!Array.isArray(data.products)) {
          if (Array.isArray(data.result)) data.products = data.result;
          else if (Array.isArray(data.data)) data.products = data.data;
          else data.products = [];
        }
        if (!Array.isArray(data.suggestions)) data.suggestions = [];
        data.query = data.query || q;
        self.lastPayload = data;
        self.activeIndex = -1;
        self.render(data);
        self.positionDropdown();
        self.open();
      })
      .catch(function (err) {
        if (err && err.name === "AbortError") return;
        if (reqId !== self._reqId) return;
        console.warn("[pls] error búsqueda", err);
        self.renderEmpty("No se pudo buscar. Intente de nuevo.");
        self.positionDropdown();
        self.open();
      });
  };

  ProductoLiveSearchWidget.prototype.renderLoading = function () {
    var scroll = this.dropdown.querySelector("[data-pls-scroll]");
    var sug = this.dropdown.querySelector("[data-pls-suggestions]");
    var foot = this.dropdown.querySelector("[data-pls-footer]");
    scroll.innerHTML = '<div class="pls-loading">Buscando…</div>';
    if (sug) {
      sug.hidden = true;
      sug.innerHTML = "";
    }
    if (foot) {
      foot.hidden = true;
      foot.innerHTML = "";
    }
  };

  ProductoLiveSearchWidget.prototype.renderEmpty = function (msg) {
    var scroll = this.dropdown.querySelector("[data-pls-scroll]");
    var sug = this.dropdown.querySelector("[data-pls-suggestions]");
    var foot = this.dropdown.querySelector("[data-pls-footer]");
    scroll.innerHTML =
      '<div class="pls-empty">' + escapeHtml(msg || "Sin resultados") + "</div>";
    if (sug) sug.hidden = true;
    if (foot) {
      foot.hidden = false;
      var q = (this.input.value || "").trim();
      foot.innerHTML =
        '<button type="button" class="pls-footer__btn" data-pls-see-all>' +
        "Buscar “" +
        escapeHtml(q) +
        "” en la tienda</button>";
      var btn = foot.querySelector("[data-pls-see-all]");
      var self = this;
      if (btn) {
        btn.addEventListener("click", function () {
          self.goToResults(q);
        });
      }
    }
  };

  ProductoLiveSearchWidget.prototype.render = function (data) {
    var products = (data && data.products) || [];
    var suggestions = (data && data.suggestions) || [];
    var q = (data && data.query) || this.input.value;
    var scroll = this.dropdown.querySelector("[data-pls-scroll]");
    var sug = this.dropdown.querySelector("[data-pls-suggestions]");
    var foot = this.dropdown.querySelector("[data-pls-footer]");
    var self = this;

    if (!products.length) {
      this.renderEmpty('No encontramos productos para “' + q + '”.');
      if (suggestions.length && sug) {
        this.renderSuggestions(suggestions);
        sug.hidden = false;
      }
      return;
    }

    scroll.innerHTML = products
      .map(function (p, i) {
        var img = p.url_imagen || "";
        var href = p.url || "#";
        var name = p.nombre || "";
        var cat = p.categoria ? '<div class="pls-item__cat">' + escapeHtml(p.categoria) + "</div>" : "";
        var priceHtml = '<span class="pls-item__price">' + money(p.precio) + "</span>";
        if (p.precio_old && Number(p.precio_old) > Number(p.precio)) {
          priceHtml +=
            '<span class="pls-item__price-old">' + money(p.precio_old) + "</span>";
        }
        return (
          '<a class="pls-item" role="option" data-pls-index="' +
          i +
          '" href="' +
          escapeHtml(href) +
          '">' +
          '<img class="pls-item__thumb" src="' +
          escapeHtml(img) +
          '" alt="" loading="lazy" onerror="this.style.opacity=0.25">' +
          '<div class="pls-item__body">' +
          cat +
          '<div class="pls-item__name">' +
          escapeHtml(name) +
          "</div>" +
          '<div class="pls-item__prices">' +
          priceHtml +
          "</div></div></a>"
        );
      })
      .join("");

    if (suggestions.length && sug) {
      this.renderSuggestions(suggestions);
      sug.hidden = false;
    } else if (sug) {
      sug.hidden = true;
      sug.innerHTML = "";
    }

    if (foot) {
      foot.hidden = false;
      foot.innerHTML =
        '<button type="button" class="pls-footer__btn" data-pls-see-all>' +
        "Ver todos los resultados de “" +
        escapeHtml(q) +
        "”</button>";
      var btn = foot.querySelector("[data-pls-see-all]");
      if (btn) {
        btn.addEventListener("click", function () {
          self.goToResults(q);
        });
      }
    }
  };

  ProductoLiveSearchWidget.prototype.renderSuggestions = function (suggestions) {
    var sug = this.dropdown.querySelector("[data-pls-suggestions]");
    var self = this;
    if (!sug) return;
    sug.innerHTML =
      '<div class="pls-suggestions__label">Búsquedas cercanas</div>' +
      '<div class="pls-suggestions__list">' +
      suggestions
        .map(function (s) {
          return (
            '<button type="button" class="pls-chip" data-pls-chip="' +
            escapeHtml(s) +
            '">' +
            escapeHtml(s) +
            "</button>"
          );
        })
        .join("") +
      "</div>";
    sug.querySelectorAll("[data-pls-chip]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var term = btn.getAttribute("data-pls-chip") || "";
        self.input.value = term;
        self.fetchSuggest(term);
        self.input.focus();
      });
    });
  };

  ProductoLiveSearchWidget.prototype.onKey = function (e) {
    if (e.key === "Escape") {
      this.close();
      return;
    }
    if (!this._open) return;

    var items = Array.prototype.slice.call(
      this.dropdown.querySelectorAll(".pls-item")
    );
    if (!items.length) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      this.activeIndex = Math.min(items.length - 1, this.activeIndex + 1);
      this.paintActive(items);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      this.activeIndex = Math.max(0, this.activeIndex - 1);
      this.paintActive(items);
    } else if (e.key === "Enter" && this.activeIndex >= 0 && items[this.activeIndex]) {
      e.preventDefault();
      window.location.href = items[this.activeIndex].getAttribute("href");
    }
  };

  ProductoLiveSearchWidget.prototype.paintActive = function (items) {
    var idx = this.activeIndex;
    items.forEach(function (el, i) {
      if (i === idx) el.classList.add("is-active");
      else el.classList.remove("is-active");
    });
    if (idx >= 0 && items[idx]) {
      items[idx].scrollIntoView({ block: "nearest" });
    }
  };

  ProductoLiveSearchWidget.prototype.open = function () {
    this._open = true;
    this.dropdown.hidden = false;
    this.dropdown.classList.add("is-open");
    this.dropdown.style.display = "flex";
  };

  ProductoLiveSearchWidget.prototype.close = function () {
    this._open = false;
    this.activeIndex = -1;
    this.dropdown.classList.remove("is-open");
    this.dropdown.hidden = true;
    this.dropdown.style.display = "none";
  };

  function boot(opts) {
    try {
      var w = new ProductoLiveSearchWidget(opts || {});
      if (w && w.input) {
        console.info("[pls] live search listo");
      }
      return w;
    } catch (e) {
      console.error("[pls] init failed", e);
      return null;
    }
  }

  window.ProductoLiveSearch = {
    init: boot,
    _instances: [],
    _booted: false,
  };

  function autoBoot() {
    if (!window.PLS_LIVE_SEARCH_CONFIG || window.ProductoLiveSearch._booted) return;
    window.ProductoLiveSearch._booted = true;
    window.ProductoLiveSearch._instances.push(boot(window.PLS_LIVE_SEARCH_CONFIG));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", autoBoot);
  } else {
    autoBoot();
  }
})(window, document);
