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
    <p>{{ __('mail.personalized_solution_resolved.cta') }}</p>
    @if(!empty($portalUrl))
        <p style="margin: 24px 0;">
            <a href="{{ $portalUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.personalized_solution_resolved.button') }}</a>
        </p>
    @endif
@endsection
