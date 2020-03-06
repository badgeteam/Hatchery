@extends('layouts.app')

@section('content')
<script type="application/ld+json">
{
  "@context" : "https://schema.org",
  "@type" : "SoftwareApplication",
  "name" : "{{ $project->name }}",
  "url" : "{{ route('projects.show', ['project' => $project->slug]) }}",
  "author" : {
    "@type" : "Person",
    "name" : "{{ $project->user->name }}"
  },
@if($project->versions()->published()->exists())
  "downloadUrl" : "{{ url($project->versions()->published()->get()->last()->zip) }}",
@endif
  "operatingSystem" : "MicroPython",
  "requirements" : "badge.team firmware",
  "softwareVersion" : "{{ $project->revision }}",
  "applicationCategory" : "{{ $project->category }}",
@if($project->votes->count() > 0)
  "aggregateRating" : {
    "@type": "AggregateRating",
    "ratingValue": "{{ $project->score }}",
    "reviewCount": "{{ $project->votes->count() }}"
  },
@endif
  "offers": {
    "@type": "Offer",
    "price": "0.00",
    "priceCurrency": "EUR"
  }
}
</script>
<div class="container-fluid">

    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <div class="panel panel-default">

                <div class="panel-heading">
                    @if($project->git)
                        <img src="{{ asset('img/git.svg') }}" alt="Git revision: {{ $project->git_commit_id}}" class="collab-icon" />
                    @endif
                    @if(!$project->collaborators->isEmpty())
                        <img src="{{ asset('img/collab.svg') }}" alt="{{ $project->collaborators()->count() . ' ' . \Illuminate\Support\Str::plural('collaborator', $project->collaborators()->count()) }}" class="collab-icon" />
                    @endif
                    <strong>{{ $project->name }}</strong> rev. {{ $project->revision }} (by {{ $project->user->name }})
                    @can('update', $project)
                    <div class="pull-right">
                        <a class="btn btn-primary btn-xs" href="{{ route('projects.edit', ['project' => $project->slug])  }}">edit</a>
                    </div>
                    @endcan
                </div>

                <div class="panel-body">
                    <div class="row">

                        <div class="col-md-8 clearfix">
                            {!! $project->descriptionHtml !!}
                        </div>
                        <div class="col-md-4 clearfix">
                            <strong>Category: {{ $project->category }}</strong>
                            <hr>
                            <strong>Status: {{ $project->status }}</strong>
                            <hr>
                            @include('projects.partials.vote-and-notify')
                            @include('projects.partials.show-compatibility')
                            @include('projects.partials.show-dependencies')
                            @include('projects.partials.show-collaborators')
                        </div>
                        @if($project->versions()->published()->count() > 0)
                        <div class="col-md-12 clearfix">
                            <a href="{{ url($project->versions()->published()->get()->last()->zip) }}" class="btn btn-default">Download latest egg (tar.gz)</a>

                        </div>
                        @endif
                        <div class="col-md-12 clearfix">
                            @include('projects.partials.show-files')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
