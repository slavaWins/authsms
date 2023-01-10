<div class="row   m-0 inp_{{$ind}}   "
     style="border: 1px solid #222;
     @error($ind) border-color:#ff2f00; @enderror
    border-radius: 12px;
    padding: 6px;">
    @if(!empty($prefix))
        <b class="col-auto inpFont">{{$prefix}}</b>
    @endif
    <input placeholder="{{$placeholder??""}}"
           class="form-control col inpNumberReal inpFont  " name="{{$ind}}"
           value="{{ old($ind) }}" required autocomplete="{{$ind}}" autofocus>


    @error($ind)
    <div class=" col-12" style="font-size: 12px; color: #ff2f00;">
        {{ $message }}
    </div>
    @enderror

</div>


