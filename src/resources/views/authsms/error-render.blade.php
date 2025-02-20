@if ($errors->any())
    <div class="error-auth-render   " style="color:#ff2f00; font-size: 0.8em;">
        @foreach ($errors->all() as $error)
            <span>{{ $error }}</span>
        @endforeach
    </div>
@endif
