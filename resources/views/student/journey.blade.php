@extends('layouts.workspace')

@section('title', $journey->title)

@section('workspace')
    @php
        $completedIds = auth()->user()->lessonProgress()
            ->where('completed', true)
            ->whereHas('lesson.module', fn ($q) => $q->where('journey_id', $journey->id))
            ->pluck('lesson_id')
            ->map(fn ($id) => (string) $id);
    @endphp

    <x-journey.journey-player
        :journey="$journey"
        :completed-lesson-ids="$completedIds"
        :is-enrolled="$enrollment !== null" />
@endsection