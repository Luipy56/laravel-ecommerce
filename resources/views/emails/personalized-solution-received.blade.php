@extends('emails.layouts.transactional')

@section('title')
    {{ __('mail.personalized_solution.subject', ['id' => $solution->id]) }}
@endsection

@section('content')
    <p>{{ __('mail.personalized_solution.greeting') }}</p>
    <p>{{ __('mail.personalized_solution.body', ['id' => $solution->id]) }}</p>
    @if(!empty($portalUrl))
        <p>{{ __('mail.personalized_solution.portal_intro') }}</p>
        <p style="margin: 24px 0;">
            <a href="{{ $portalUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.personalized_solution.portal_button') }}</a>
        </p>
    @endif
@endsection
