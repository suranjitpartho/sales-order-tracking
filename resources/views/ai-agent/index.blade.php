@extends('layouts.app')
@section('title', 'AI Agent')

@section('content')

    <h2 class="section-title">My AI SQL Agent</h2>
    
    <form id="askForm" class="form-wrapper" method="POST" action="{{ route('ai-agent.ask') }}">
        @csrf
        <div class="form-group">
            <input type="text" name="question" placeholder="Ask something like: 'What is the average buyer age?'" required>
        </div>
        <button type="submit" class="btn">Ask</button>
    </form>
    <br>

    <div id="timerBox" style="display:none;">
        <strong>Processing... Elapsed Time: <span id="elapsed">0</span> seconds</strong>
    </div>
    <br>

    @if(isset($answer))
        <div>
            <p><i class="fa-solid fa-user"></i> {{ $query }}</p><br>
            <p><i class="fa-solid fa-robot"></i> {{ $answer }}</p><br>

            @if(isset($sql))
                <div>
                    <h4>SQL Used:</h4>
                    <pre>{{ $sql }}</pre>
                </div>
            @endif
        </div>
    @endif
    
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


