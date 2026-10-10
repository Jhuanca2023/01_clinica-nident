<div class="modal fade" id="idOpenModalDetalle" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content clsModalCarrito">

            <!-- Franja de éxito -->
            <div class="clsModalCarritoStrip">
                <div class="clsModalCarritoStripIco">
                    <i class="fas fa-check"></i>
                </div>
                <div class="clsModalCarritoStripTexto">
                    <div class="clsModalCarritoStripTitulo">@lang('SHOPDETAILS.TITULO.MODAL.AGREGADO')</div>
                    <div class="clsModalCarritoStripSub">El producto fue añadido correctamente</div>
                </div>
                <button type="button" class="clsModalCarritoStripCerrar" data-dismiss="modal" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Cuerpo -->
            <div class="clsModalCarritoBody">
                <div class="clsModalCarritoGrid">

                    <!-- Panel izquierdo: producto -->
                    <div class="clsModalCarritoPanel clsModalCarritoPanelIzq">

                        <!-- Vista individual -->
                        <div id="modalVistaSingle">
                            <div class="clsModalProdCard">
                                <div class="clsModalProdCardImg">
                                    <img src="" id="idDescripcion_Imagen" alt="Producto">
                                </div>
                                <div class="clsModalProdCardInfo">
                                    <p class="clsModalProdCardNombre clsDescripcion_Nombre">—</p>
                                    <div class="clsModalProdCardRow">
                                        <span class="clsModalProdCardLabel">Precio:</span>
                                        <span class="clsModalProdCardVal">S/ <span class="clsDescripcion_Precio">0.00</span></span>
                                    </div>
                                    <div class="clsModalProdCardRow">
                                        <span class="clsModalProdCardLabel">Cantidad:</span>
                                        <span class="clsModalProdCardBadge clsDescripcion_CountCart">0</span>
                                    </div>
                                    <div class="clsModalProdCardRow clsModalProdCardRowSubtotal">
                                        <span class="clsModalProdCardLabel">Subtotal:</span>
                                        <span class="clsModalProdCardSubtotal">S/ <span class="clsDescripcion_SubTotal">0.00</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vista múltiple -->
                        <div id="modalVistaMultiple" style="display:none;">
                            <div class="clsModalMultiHeader">
                                <span>Productos en carrito</span>
                                <span class="clsModalMultiBadge" id="modalCantidadProductos">0</span>
                            </div>
                            <div class="modal-productos-lista" id="modalProductosLista"></div>
                        </div>

                    </div>

                    <!-- Divisor vertical -->
                    <div class="clsModalCarritoDividerV"></div>

                    <!-- Panel derecho: resumen -->
                    <div class="clsModalCarritoPanel clsModalCarritoPanelDer">
                        <div class="clsModalResumenTitulo">Resumen</div>
                        <div class="clsModalResumenFila">
                            <span class="clsModalResumenLabel">Artículos</span>
                            <span class="clsModalResumenBadge clsDetalle_CountCart">0</span>
                        </div>
                        <div class="clsModalResumenFila">
                            <span class="clsModalResumenLabel">IGV (18%)</span>
                            <span class="clsModalResumenVal clsDescripcion_Igv">S/ 0.00</span>
                        </div>
                        <div class="clsModalResumenDivider"></div>
                        <div class="clsModalResumenTotal">
                            <span class="clsModalResumenTotalLabel">Total</span>
                            <span class="clsModalResumenTotalVal"><span class="clsDetalle_Total">0.00</span></span>
                        </div>
                    </div>

                </div>

                <!-- Sugeridos — ancho completo del modal -->
                <div class="sugeridos-section sugeridos-full-width mt-3" id="sugeridosSection" style="display:none;">
                    <div class="sugeridos-header">
                        <h6 class="sugeridos-title">
                            <i class="fas fa-star" style="color:#ff6b6b;"></i>
                            Completa tu compra
                        </h6>
                        <div class="sugeridos-nav">
                            <button type="button" class="sugeridos-nav-btn" id="sugeridosPrev"><i class="fas fa-chevron-left"></i></button>
                            <button type="button" class="sugeridos-nav-btn" id="sugeridosNext"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                    <div class="sugeridos-carousel-wrapper">
                        <div class="sugeridos-carousel" id="sugeridosCarousel"></div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="clsModalCarritoFooter">
                <button type="button" class="clsModalBtnSeguir" data-dismiss="modal">
                    <i class="fas fa-arrow-left" style="margin-right:6px;"></i> Seguir comprando
                </button>
                <a href="/web_shopDetail_lista" class="clsModalBtnFinalizar">
                    <i class="fas fa-credit-card" style="margin-right:6px;"></i> Finalizar Compra
                </a>
            </div>

        </div>
    </div>
</div>
