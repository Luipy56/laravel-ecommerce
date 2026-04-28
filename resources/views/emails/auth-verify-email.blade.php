@extends('emails.layouts.transactional')

@section('title', $emailTitle)

@section('content')
    <p style="margin: 0 0 16px;">{{ __('mail.verify_email.greeting', [], $mailLocale) }}</p>
    <p style="margin: 0 0 24px;">{{ __('mail.verify_email.body', [], $mailLocale) }}</p>

    <p style="margin: 24px 0; text-align: center;">
        <a href="{{ $actionUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.verify_email.button', [], $mailLocale) }}</a>
    </p>

    <div style="margin: 24px 0 0; padding: 14px 16px; background: #fafafa; border: 1px solid #e4e4e7; border-radius: 10px;">
        <p style="margin: 0 0 8px; font-size: 12px; font-weight: 600; color: #52525b; text-transform: uppercase; letter-spacing: 0.02em;">{{ __('mail.verify_email.link_fallback_label', [], $mailLocale) }}</p>
        <p style="margin: 0; font-size: 13px; word-break: break-all; color: #18181b;"><a href="{{ $actionUrl }}" style="color: #F75211;">{{ $actionUrl }}</a></p>
    </div>

    <p style="margin: 24px 0 0; font-size: 14px; color: #52525b;">{{ __('mail.verify_email.line2', [], $mailLocale) }}</p>
@endsection
