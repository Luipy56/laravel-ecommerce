@extends('emails.layouts.transactional')

@section('title', $emailTitle)

@section('content')
    <p>{{ __('mail.personalized_solution_resolved.greeting', [], $mailLocale) }}</p>
    <p>{{ __('mail.personalized_solution_resolved.body', ['id' => $solution->id], $mailLocale) }}</p>
    @if(!empty($solution->resolution))
        <p style="white-space: pre-wrap;">{{ \Illuminate\Support\Str::limit(strip_tags($solution->resolution), 400) }}</p>
    @endif
    <p style="margin: 20px 0 0;">{{ __('mail.personalized_solution_resolved.cta', [], $mailLocale) }}</p>
    @if(!empty($portalUrl))
        <p style="margin: 24px 0; text-align: center;">
            <a href="{{ $portalUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.personalized_solution_resolved.button', [], $mailLocale) }}</a>
        </p>
    @endif
@endsection
