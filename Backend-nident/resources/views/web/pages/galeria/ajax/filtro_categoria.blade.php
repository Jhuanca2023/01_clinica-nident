         <form id="filtros-form">
            @csrf
             @foreach($estructuraGaleriaCategoria as $grupo)

             @if($grupo['subcategorias'][0]['nombre'] !="" && $grupo['subcategorias']->count() > 0)
             <div class="widget-collapsible {{ $grupo['categoria']['activo'] ? 'widget-active' : '' }}">
                 <h3 class="widget-title">
                     <a data-toggle="collapse" href="#widget-{{ $grupo['categoria']['id'] }}" role="button"
                         class="filtro-titulo d-flex align-items-center justify-content-between w-100"
                         data-value="{{ $grupo['categoria']['id'] }}-null">
                         <span class="d-flex align-items-center">
                             <i class="fas fa-chevron-down mr-2 widget-icon"></i>
                             {{ $grupo['categoria']['nombre'] }}
                         </span>
                         <span class="d-flex align-items-center" style="margin-right: 2rem;">
                             <span class="badge badge-pill badge-primary mr-4">{{ $grupo['categoria']['total_productos'] }}</span>
                             @if($grupo['categoria']['activo'])
                             <i class="fas fa-check-circle text-success ml-1"></i>
                             @endif
                         </span>
                     </a>
                 </h3>

                 <div class="collapse show" id="widget-{{ $grupo['categoria']['id'] }}">
                     <div class="widget-body">
                         <!-- Subcategorías -->
                         <div class="filter-items">
                             @foreach($grupo['subcategorias'] as $subcategoria)
                             <div class="filter-item {{ $subcategoria['activo'] ? 'filter-item-active' : '' }}">
                                 <div class="custom-control custom-checkbox d-flex align-items-center justify-content-between">
                                     <div>
                                         <input type="checkbox" class="custom-control-input filtro-subcategoria"
                                             id="cat-{{ $grupo['categoria']['id'] }}-{{ $subcategoria['id'] }}"
                                             value="{{ $grupo['categoria']['id'] }}-{{ $subcategoria['id'] }}"
                                             {{ in_array($grupo['categoria']['id'].'-'.$subcategoria['id'], $filtrosSeleccionados ?? []) ? 'checked' : '' }}>
                                         <label class="custom-control-label" for="cat-{{ $grupo['categoria']['id'] }}-{{ $subcategoria['id'] }}">
                                             {{ $subcategoria['nombre'] }}
                                         </label>
                                     </div>
                                     <span class="badge badge-pill badge-light flex-shrink-0">{{ $subcategoria['total_productos'] }}</span>
                                 </div>
                             </div>
                             @endforeach
                         </div>
                     </div>
                 </div>
             </div>
             @else
             <div class="widget  mb-4 {{ $grupo['categoria']['activo'] ? 'widget-active' : '' }}">
                 <h3 class="widget-title">
                     <a href="#" role="button"
                         class="filtro-titulo d-flex align-items-center justify-content-between w-100"
                         data-value="{{ $grupo['categoria']['id'] }}-null">
                         <span class="d-flex align-items-center">
                             <i class="fas fa-tag mr-2 widget-icon"></i>
                             {{ $grupo['categoria']['nombre'] }}
                         </span>
                         <span class="d-flex align-items-center">
                             <span class="badge badge-pill badge-primary">{{ $grupo['categoria']['total_productos'] }}</span>
                             @if($grupo['categoria']['activo'])
                             <i class="fas fa-check-circle text-success ml-1"></i>
                             @endif
                         </span>
                     </a>
                 </h3>
             </div>
             @endif
             @endforeach
         </form>

         <style>
         </style>