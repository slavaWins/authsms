@extends('layouts.app')

@section('app-col')



    <div class="container">

        <div class=" ">

            <style>
                .navbarMain {
                    display: none !important;
                }

                body, html {
                    height: 100%;
                }

                body {
                    background-image: url(/img/background-t.svg);
                    background-size: cover;
                }

                .btn-submit-auth {
                    border-radius: 33px;
                }
                .btn-round-back-auth {
                    height: 40px;
                    width: 40px;
                    background: #fff;
                    border-radius: 100%;
                    display: block;
                    box-shadow: 1px 6px 7px rgb(0 0 0 / 38%);
                    text-align: center;
                    color: #000;
                    font-size: 18px;
                    padding-top: 5px;
                    border: 1px solid #fff;
                    position: absolute;
                    left: -20px;
                    cursor: pointer;
                    font-weight: 500;
                    text-decoration: none;
                }

                .inp_phone_auth ._messageBottom{
                    font-size: 12px; color: #ff2f00;
                }
                .inp_phone_auth{
                    border: 1px solid #222;
                    border-radius: 12px;
                    padding: 6px;
                }
                .inpNumberReal {
                    border: none;
                    padding: 0px;
                }

                .inpFont {
                    font-weight: 500;
                    font-size: 24px;
                }

                .inpNumberReal:focus {
                    outline: 0 !important;
                    -webkit-tap-highlight-color: transparent;
                    border: none !important;
                    box-shadow: none !important;
                }

                .inp_code input {
                    text-align: center;
                }
            </style>


            <div class="row justify-content-center" style="padding-top: 20%;">
                <div class="col-xs-12 p-0 " style="max-width: 360px;">
                    <div class="card p-0" style="border-radius: 25px;">
                        <div class="card-body  ">

                            <a class="navbar-brand   mb-2 " href="/"
                               style="text-align: center; display: block;     padding-top: 1px;">
                                <img src="/img/logo.svg" height="28" loading="lazy">
                            </a>

                            @yield('content_auth')

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
