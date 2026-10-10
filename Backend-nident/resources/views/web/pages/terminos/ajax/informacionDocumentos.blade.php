<div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel">{{ $terminosData[0]->titulo }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
<div class="container-fluid">
    {!! (count($terminosData)!=0?str_replace("\n", "<br />", $terminosData[0]->descripcion):'')!!}
    </div></div>
<script>


</script>