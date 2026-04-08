@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error') || $errors->has('import') || $errors->has('generate') || $errors->has('delete'))
<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-exclamation-circle mr-2"></i>
    @if(session('error'))
        {{ session('error') }}
    @endif
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('warning'))
<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif
