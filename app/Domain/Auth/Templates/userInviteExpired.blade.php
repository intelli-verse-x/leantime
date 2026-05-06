@extends($layout)

@section('content')

<div class="regcontent tw-text-center" style="padding: 40px 20px;">

    <x-global::undrawSvg image="undraw_time_management_30iu.svg" maxWidth="50%" maxHeight="250px" />

    <h2 style="margin-top: 30px;">{{ __('headlines.invite_expired') }}</h2>

    <p style="margin-top: 16px; max-width: 480px; margin-left: auto; margin-right: auto;">
        {{ __('text.invite_link_expired') }}
    </p>

    <p style="margin-top: 24px;">
        <a href="{{ BASE_URL }}/auth/login" class="btn btn-secondary">
            {{ __('links.back_to_login') }}
        </a>
    </p>

</div>

@endsection
