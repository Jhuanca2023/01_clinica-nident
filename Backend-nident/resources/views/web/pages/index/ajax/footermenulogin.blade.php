    <!-- 0 TOP PARA DESPLEGAR LA PAGINA -->
    <button id="scroll-top" title="Back to Top"><i class="icon-arrow-up"></i></button>
    <!-- 1 MENU RESPONSIVE -->
    <div class="mobile-menu-overlay"></div>
    <!-- 2 MENU RESPONSIVE -->
    <div class="mobile-menu-container mobile-menu-light">
        <div class="mobile-menu-wrapper">
            <span class="mobile-menu-close"><i class="icon-close"></i></span>

            <form id="searchForm_" action="{{ route('web_gallery') }}" method="GET" class="mobile-search searchFormx">
                <label for="mobile-search" class="sr-only">Search</label>
                <input type="search"
                    class="form-control productSearchx"
                    id="productSearch_"
                    placeholder="Buscar productos..."
                    required
                    value="{{ request('q') }}">
                <button class="btn btn-primary searchButtonx" type="submit" type="submit" id="searchButton_"><i class="icon-search"></i></button>
            </form>
            
            <div id="searchResults_" class="search-results searchResultsx"></div>
            <ul class="nav nav-pills-mobile nav-border-anim" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="mobile-menu-link" data-toggle="tab" href="#mobile-menu-tab" role="tab" aria-controls="mobile-menu-tab" aria-selected="true">Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="mobile-cats-link" data-toggle="tab" href="#mobile-cats-tab" role="tab" aria-controls="mobile-cats-tab" aria-selected="false">Categorias</a>
                </li>
            </ul>

            <div class="tab-content mobil-tab">
                <div class="tab-pane fade show active" id="mobile-menu-tab" role="tabpanel" aria-labelledby="mobile-menu-link">
                    <nav class="mobile-nav">
                        <ul class="mobile-menu">
                            <li class="active"><a href="/">Inicio</a></li>
                            <li><a href="/web_about">Nosotros</a></li>
                            <li><a href="/web_contact">Contácto</a></li>
                        </ul>
                    </nav>
                </div>
                <div class="tab-pane fade" id="mobile-cats-tab" role="tabpanel" aria-labelledby="mobile-cats-link">
                    <nav class="mobile-cats-nav">
                        <ul class="menu-vertical sf-arrows">
                            @foreach(CategoryHelper::getFullMenu() as $category)
                            <li class="item-lead">
                                <!-- Enlace para la categoría principal -->
                                <a href="{{ url('/web_gallery/' . (int)$category['id']) }}">
                                    {{ $category['name'] }}
                                </a>

                                @if(count($category['subcategories']) > 0)
                                <ul>
                                    @foreach($category['subcategories'] as $subcategory)
                                    <li>
                                        <!-- Enlace para subcategoría: /web_gallery/{categoria}/{subcategoria} -->
                                        <a href="{{ url('/web_gallery/' . (int)$category['id'] . '/' . (int)$subcategory['id']) }}">
                                            {{ $subcategory['name'] }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
            </div>

            <ul class="social-links clsRedesSocialesAlign">
                @isset($footerData[0]->red_social_facebook)
                <li><a href="{{ $footerData[0]->red_social_facebook }}" title="Facebook" target="_blank" class="clsMovilRedesColor"><i class="fab fa-facebook-f text-white"></i></a></li>
                @endisset

                @isset($footerData[0]->red_social_twitter)
                <li><a href="{{ $footerData[0]->red_social_twitter }}" title="Twitter" target="_blank" class="clsMovilRedesColor"><i class="fab fa-twitter text-white"></i></a></li>
                @endisset

                @isset($footerData[0]->red_social_instagram)
                <li><a href="{{ $footerData[0]->red_social_instagram }}" title="Instagram" target="_blank" class="clsMovilRedesColor"><i class="fab fa-instagram text-white"></i></a></li>
                @endisset

                @isset($footerData[0]->red_social_linkedin)
                <li><a href="{{ $footerData[0]->red_social_linkedin }}" title="Linkedin" target="_blank" class="clsMovilRedesColor"><i class="fab fa-linkedin-in text-white"></i></a></li>
                @endisset

                @isset($footerData[0]->red_social_youtobe)
                <li><a href="{{ $footerData[0]->red_social_youtobe }}" title="Youtube" target="_blank" class="clsMovilRedesColor"><i class="fab fa-youtube text-white"></i></a></li>
                @endisset
            </ul>
        </div>
    </div>