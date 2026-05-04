@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.admin_personalized_improvement.subject', ['id' => $solution->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.admin_personalized_improvement.intro', ['id' => $solution->id]) }}</p>
    <p><strong>{{ __('mail.admin_personalized_improvement.email') }}</strong> {{ $solution->email ?? '' }}</p>
    <p style="margin-top: 16px;"><strong>{{ __('mail.admin_personalized_improvement.message') }}</strong></p>
    <p style="white-space: pre-wrap; background: #fafafa; padding: 12px; border-radius: 8px;">{{ $clientMessage }}</p>
    @if(!empty($adminUrl))
        <p style="margin: 24px 0;">
            <a href="{{ $adminUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.admin_personalized_improvement.button') }}</a>
        </p>
    @endif
@endsection
