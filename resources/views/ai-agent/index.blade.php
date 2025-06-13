@extends('layouts.app')
@section('title', 'AI Agent')

@section('content')

    <h2 class="section-title">My AI SQL Agent</h2>

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
        {{ $errors->first() }}
        </div>
    @endif
    
    <form id="askForm" class="form-wrapper" method="POST" action="{{ route('ai-agent.ask') }}">
        @csrf
        <div class="form-group-horizontal">
            <input type="text" name="question" placeholder="Ask something...." autocomplete="off" required>
            <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </form>
    <br>

    <div id="timerBox" style="display:none;">
        <strong>Processing... Elapsed Time: <span id="elapsed">0</span> seconds</strong>
    </div>
    <br>

    @if(isset($summary))
        <div>
            <p><i class="fa-solid fa-user"></i> {{ $query }}</p><br>
        </div>
    @endif
    

    @if(! empty($tableHtml))
        <div class="mb-6">
        <div class="overflow-auto border border-gray-200 rounded">
            {!! $tableHtml !!}
        </div>
        </div>
    @endif

    <br>

    @if(! empty($chartBase64))
        <div class="mb-6">
        <img
            src="data:image/png;base64,{{ $chartBase64 }}"
            alt="AI-generated chart"
            class="w-full h-auto rounded shadow-sm"
        />
        </div>
    @endif

    <br>

    @if(isset($summary))
        <div>
            <p><i class="fa-solid fa-robot"></i> {!! nl2br(e($summary)) !!}</p>
        </div>
    @endif
    <br>

    @isset($sql)
        <div class="mb-4">
        <h4 class="text-md font-medium text-gray-600 mb-1">SQL Used</h4>
        <pre class="bg-gray-100 p-3 rounded text-sm overflow-auto">{{ $sql }}</pre>
        </div>
    @endisset

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('askForm');
            const timerBox = document.getElementById('timerBox');
            const elapsedSpan = document.getElementById('elapsed');
            let interval;

            form.addEventListener('submit', function () {
                timerBox.style.display = 'block';
                let seconds = 0;
                elapsedSpan.textContent = seconds;

                interval = setInterval(() => {
                    seconds++;
                    elapsedSpan.textContent = seconds;
                }, 1000);
            });

            // Stop the timer if coming back with response
            if (document.querySelector('div strong')?.textContent.includes('Answer')) {
                clearInterval(interval);
            }
        });
    </script>
@endpush


