<div class="row   m-0 inp_{{$ind}}  inp_phone_auth "
     style="@error($ind) border-color:#ff2f00; @enderror ">
    @if(!empty($prefix))
        <b class="col-auto inpFont">{{$prefix}}</b>
    @endif
    <input placeholder="{{$placeholder??""}}"
           @if($isDisabledAutoComplite ?? false)
               autocomplete="off"
           @endif
           class="form-control col inpNumberReal inpFont no-outline-input  " name="{{$ind}}" type="{{$type??"text"}}"
           value="{{ $value ?? old($ind) }}" required autocomplete="{{$ind}}" autofocus>



    @error($ind)
    <div class=" col-12 _messageBottom">
        {{ $message }}
    </div>
    @enderror

</div>


