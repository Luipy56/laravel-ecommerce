@extends('emails.layouts.transactional')

@section('title', $emailTitle)

@section('content')
    <p style="margin: 0 0 16px;">{{ __('mail.verify_email.greeting', [], $mailLocale) }}</p>
    <p style="margin: 0 0 24px;">{{ __('mail.verify_email.body', [], $mailLocale) }}</p>

    <p style="margin: 24px 0; text-align: center;">
        <a href="{{ $actionUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.verify_email.button', [], $mailLocale) }}</a>
    </p>

    <p style="margin: 20px 0 0; font-size: 12px; color: #71717a;">{{ __('mail.verify_email.link_fallback_label', [], $mailLocale) }}: <a href="{{ $actionUrl }}" style="color: #F75211; word-break: break-all;">{{ $actionUrl }}</a></p>

    <p style="margin: 24px 0 0; font-size: 14px; color: #52525b;">{{ __('mail.verify_email.line2', [], $mailLocale) }}</p>
@endsection
