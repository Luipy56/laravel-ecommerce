@extends('emails.layouts.transactional')

@section('title', $emailTitle)

@section('content')
    <p>{{ __('mail.personalized_solution.greeting', [], $mailLocale) }}</p>
    <p>{{ __('mail.personalized_solution.body', ['id' => $solution->id], $mailLocale) }}</p>

    <div style="margin: 24px 0; padding: 16px 18px; background: #fafafa; border: 1px solid #e4e4e7; border-radius: 10px;">
        <p style="margin: 0 0 12px; font-size: 14px; font-weight: 700; color: #18181b;">{{ __('mail.personalized_solution.preview_heading', [], $mailLocale) }}</p>
        <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; color: #52525b; text-transform: uppercase; letter-spacing: 0.02em;">{{ __('mail.personalized_solution.preview_description', [], $mailLocale) }}</p>
        <div style="margin: 0 0 16px; font-size: 15px; color: #18181b; white-space: pre-wrap; word-break: break-word;">{!! nl2br(e($problemPreview)) !!}</div>
        @if(!empty($descriptionTruncated))
            <p style="margin: 0 0 16px; font-size: 13px; color: #52525b;">{{ __('mail.personalized_solution.preview_truncated', [], $mailLocale) }}</p>
        @endif
        @if(!empty($solution->phone))
            <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; color: #52525b; text-transform: uppercase; letter-spacing: 0.02em;">{{ __('mail.personalized_solution.preview_phone', [], $mailLocale) }}</p>
            <p style="margin: 0 0 16px; font-size: 15px; color: #18181b;">{{ e($solution->phone) }}</p>
        @endif
        @if(!empty($addressLines))
            <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; color: #52525b; text-transform: uppercase; letter-spacing: 0.02em;">{{ __('mail.personalized_solution.preview_address', [], $mailLocale) }}</p>
            <div style="margin: 0 0 16px; font-size: 15px; color: #18181b; white-space: pre-wrap; word-break: break-word;">{!! nl2br(e(implode("\n", $addressLines))) !!}</div>
        @endif
        @if(!empty($attachmentFilenames))
            <p style="margin: 0 0 6px; font-size: 12px; font-weight: 600; color: #52525b; text-transform: uppercase; letter-spacing: 0.02em;">{{ __('mail.personalized_solution.preview_attachments', [], $mailLocale) }}</p>
            <ul style="margin: 0; padding-left: 1.1em; font-size: 15px; color: #18181b;">
                @foreach($attachmentFilenames as $name)
                    <li style="margin: 4px 0;">{{ e($name) }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    @if(!empty($portalUrl))
        <p style="margin: 20px 0 0;">{{ __('mail.personalized_solution.portal_intro', [], $mailLocale) }}</p>
        <p style="margin: 24px 0; text-align: center;">
            <a href="{{ $portalUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.personalized_solution.portal_button', [], $mailLocale) }}</a>
        </p>
    @endif
@endsection
