@extends('admin.layout')
@section('admin-setting-content')
    <div class="container-xxl">
        @if (Session::get('success'))
            <div class=" alert alert-success">
                {{ Session::get('success') }}
            </div>
        @endif
        @if (Session::get('error'))
            <div class="alert alert-danger">
                {{ Session::get('error') }}
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4><strong>General Application Settings</strong>
                            <h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>App Logo</strong>
                        <button class="btn btn-link" type="button" data-bs-toggle="collapse"
                            data-bs-target="#deal-settings">
                            Edit
                        </button>
                    </div>
                    <div id="deal-settings" class="collapse">
                        <div class="card-body">
                            <form method="POST" action="{{ route('settings.logo') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Logo</label>
                                    <div class="col-sm-6">
                                        <input type="file" class="form-control" id="horizontalInput1" name="logo">
                                        @if ($errors->has('logo'))
                                            <span class="text-danger">{{ $errors->first('logo') }}</span>
                                        @endif
                                        @if (!empty($settings->logo))
                                            <img src="{{ asset('media/setting/' . $settings->logo) }}" alt="Current Logo"
                                                style="max-height: 60px;">
                                        @endif
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Splash Screen Metadata</strong>
                        <button class="btn btn-link" type="button" data-bs-toggle="collapse"
                            data-bs-target="#screen-settings">
                            Edit
                        </button>
                    </div>
                    <div id="screen-settings" class="collapse">
                        <div class="card-body">
                            <form method="POST" action="{{ route('settings.secscreen') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Second Splash Screen Image</label>
                                    <div class="col-sm-6">
                                        <input type="file" class="form-control" id="horizontalInput1" name="sec_spl_srn_image">
                                        @if ($errors->has('sec_spl_srn_image'))
                                            <span class="text-danger">{{ $errors->first('sec_spl_srn_image') }}</span>
                                        @endif
                                        @if (!empty($settings->sec_spl_srn_image))
                                            <img src="{{ asset('media/setting/' . $settings->sec_spl_srn_image) }}" alt="Current Logo"
                                                style="max-height: 60px;">
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Second Splash Screen Title</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="horizontalInput1" name="sec_spl_srn_title" value="{{$settings->sec_spl_srn_title}}">
                                        @if ($errors->has('sec_spl_srn_title'))
                                            <span class="text-danger">{{ $errors->first('sec_spl_srn_title') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Second Splash Screen Description</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="horizontalInput1" name="sec_spl_srn_desc" value="{{$settings->sec_spl_srn_desc}}">
                                        @if ($errors->has('sec_spl_srn_desc'))
                                            <span class="text-danger">{{ $errors->first('sec_spl_srn_desc') }}</span>
                                        @endif 
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Business & Consumer Splash Screen Metadata</strong>
                        <button class="btn btn-link" type="button" data-bs-toggle="collapse"
                            data-bs-target="#screentwo-settings">
                            Edit
                        </button>
                    </div>
                    <div id="screentwo-settings" class="collapse">
                        <div class="card-body">
                            <form method="POST" action="{{ route('settings.busconscreen') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Business Splash Screen Image</label>
                                    <div class="col-sm-6">
                                        <input type="file" class="form-control" id="horizontalInput1" name="business_spl_srn_image">
                                        @if ($errors->has('business_spl_srn_image'))
                                            <span class="text-danger">{{ $errors->first('business_spl_srn_image') }}</span>
                                        @endif
                                        @if (!empty($settings->business_spl_srn_image))
                                            <img src="{{ asset('media/setting/' . $settings->business_spl_srn_image) }}" alt="Current Logo"
                                                style="max-height: 60px;">
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Business Splash Screen Title</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="horizontalInput1" name="business_spl_srn_title" value="{{$settings->business_spl_srn_title}}">
                                        @if ($errors->has('business_spl_srn_title'))
                                            <span class="text-danger">{{ $errors->first('business_spl_srn_title') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Business Second Splash Screen Image</label>
                                    <div class="col-sm-6">
                                        <input type="file" class="form-control" id="horizontalInput1" name="business_sec_spl_srn_image">
                                        @if ($errors->has('business_sec_spl_srn_image'))
                                            <span class="text-danger">{{ $errors->first('business_sec_spl_srn_image') }}</span>
                                        @endif
                                        @if (!empty($settings->business_sec_spl_srn_image))
                                            <img src="{{ asset('media/setting/' . $settings->business_sec_spl_srn_image) }}" alt="Current Logo"
                                                style="max-height: 60px;">
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Consumer Splash Screen Image</label>
                                    <div class="col-sm-6">
                                        <input type="file" class="form-control" id="horizontalInput1" name="consumer_spl_srn_image">
                                        @if ($errors->has('consumer_spl_srn_image'))
                                            <span class="text-danger">{{ $errors->first('consumer_spl_srn_image') }}</span>
                                        @endif
                                        @if (!empty($settings->consumer_spl_srn_image))
                                            <img src="{{ asset('media/setting/' . $settings->consumer_spl_srn_image) }}" alt="Current Logo"
                                                style="max-height: 60px;">
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label">Consumer Splash Screen Title</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="horizontalInput1" name="consumer_spl_srn_title" value="{{$settings->consumer_spl_srn_title}}">
                                        @if ($errors->has('consumer_spl_srn_title'))
                                            <span class="text-danger">{{ $errors->first('consumer_spl_srn_title') }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
