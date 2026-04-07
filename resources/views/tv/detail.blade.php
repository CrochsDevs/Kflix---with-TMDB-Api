@extends('layouts.app')

@section('title', $tv['name'] ?? 'TV Show - KFLIX')

@section('content')
<style>
    .detail-container { max-width: 1400px; margin: 0 auto; padding: 20px; }
</style>
<div class="detail-container">
    @php
        $poster = $tv['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $tv['poster_path'] : "https://via.placeholder.com/500x750?text=No+Poster";
        $backdrop = $tv['backdrop_path'] ? "https://image.tmdb.org/t/p/original" . $tv['backdrop_path'] : $poster;
    @endphp
    <div style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(10,10,10,1) 100%), url('{{ $backdrop }}'); background-size: cover; background-position: center; padding: 60px 30px; border-radius: 12px; margin-bottom: 40px; display: flex; gap: 30px; align-items: flex-end;">
        <div style="flex-shrink:0;width:250px;border-radius:12px;overflow:hidden;"><img src="{{ $poster }}" alt="{{ $tv['name'] }}" onerror="this.src='https://via.placeholder.com/500x750?text=No+Image'"></div>
        <div>
            <h1 style="font-size:2.5rem;margin-bottom:10px;">{{ $tv['name'] }}</h1>
            <div style="display:flex;gap:15px;margin-bottom:10px;">
                <span>&#11088; {{ number_format($tv['vote_average'] ?? 0, 1) }}</span>
                <span>{{ $tv['first_air_date'] ? substr($tv['first_air_date'],0,4) : 'N/A' }}</span>
                <span>{{ count($tv['seasons'] ?? []) }} Season(s)</span>
            </div>
            @if(!empty($tv['genres']))<p>{{ implode(', ', array_column($tv['genres'], 'name')) }}</p>@endif
            <div style="margin:20px 0;"><p>{{ $tv['overview'] }}</p></div>
            <button onclick="window.location.href='/watch?id={{ $tv['id'] }}&type=tv&season=1&episode=1'" style="background:var(--primary);color:#fff;border:none;padding:12px 30px;border-radius:30px;cursor:pointer;font-size:1.1rem;font-weight:600;"><i class="fas fa-play"></i> Play</button>
        </div>
    </div>
</div>
@endsection
