@extends('layouts.app')

@section('content')

        <app v-bind:current-user='{!! Auth::user()->toJson() !!}'></app>

@endsection
