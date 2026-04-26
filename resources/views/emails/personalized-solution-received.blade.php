@extends('emails.layouts.transactional')

@section('title', $emailTitle)

@section('content')
    <p>{{ __('mail.personalized_solution.greeting', [], $mailLocale) }}</p>
    <p>{{ __('mail.personalized_solution.body', ['id' => $solution->id], $mailLocale) }}</p>
    @if(!empty($portalUrl))
        <p style="margin: 20px 0 0;">{{ __('mail.personalized_solution.portal_intro', [], $mailLocale) }}</p>
        <p style="margin: 24px 0; text-align: center;">
            <a href="{{ $portalUrl }}" style="display: inline-block; padding: 12px 20px; background: #F75211; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">{{ __('mail.personalized_solution.portal_button', [], $mailLocale) }}</a>
        </p>
    @endif
@endsection
