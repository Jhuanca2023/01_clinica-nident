<section class="counter-section wow fadeInUp animated" data-wow-delay="370ms">
  <div class="counter-books" aria-hidden="true">
    @foreach(['📖','📚','📄','📖','📚','📄','📖','📚','📄','📖','📚','📄'] as $emoji)
    <span class="book-item">{{ $emoji }}</span>
    @endforeach
  </div>
  <div class="container-fluid">
    <div class="row row-gap-5">
      @foreach($contadores as $c)
      <div class="col-lg-3 col-6">
        <div class="counter-count">
          <span class="count h3 dark-gray fw-400">{{ $c->valor }}</span>
          <span class="h3 dark-gray fw-400">{{ $c->sufijo ?? '+' }}</span>
          <h3 class="fw-400 color-primary">{{ $c->etiqueta }}</h3>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
