@extends('emails.layouts.transactional')

@section('title')
    {{ $customSubject ?? '' }}
@endsection

@section('content')
    {!! nl2br(e($bodyText)) !!}
@endsection
