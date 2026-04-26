@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.personalized_solution_resolved.subject', ['id' => $solution->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.personalized_solution_resolved.greeting') }}</p>
    <p>{{ __('mail.personalized_solution_resolved.body', ['id' => $solution->id]) }}</p>
    @if(!empty($solution->resolution))
        <p style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit(strip_tags($solution->resolution), 400) }}</p>
    @endif
    @if(!empty($solution->public_token))
        <p style="margin: 16px 0 0; font-size: 15px; color: #3f3f46;">{{ __('mail.personalized_solution_resolved.reference_code_label') }}</p>
        <p style="margin: 6px 0 0; font-size: 13px; font-family: ui-monospace, Consolas, monospace; color: #18181b; word-break: break-all;">{{ $solution->public_token }}</p>
    @endif
    <p style="margin: 20px 0 0;">{{ __('mail.personalized_solution_resolved.cta') }}</p>
    @if(!empty($portalUrl))
        <p style="margin: 24px 0; text-align: center;">
            <a href="{{ $portalUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.personalized_solution_resolved.button') }}</a>
        </p>
    @endif
@endsection
