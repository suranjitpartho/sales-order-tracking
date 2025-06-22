@extends('layouts.app')
@section('title', 'Data Analysis Agent')

@section('content')

<div class="agent-canvas">

    <div class="left-pannel">
        <div class="database-list">
            <h4>AVAILABLE TABLES</h4>
        </div>
        <div class="sql-pannel">
            @isset($sql)
                <div class="sql-box">
                    <h4>SQLQUERY:</h4>
                    <pre><code class="language-sql">{{ $sql }}</code></pre>
                </div>
            @endisset
        </div>
    </div>

    <div class="main-pannel">
        <h2 class="section-title">DATA ANALYSIS AGENT</h2>

        <div class="chat-content">

            @if(isset($question))
                <div class="user-input">
                    <div class="bubble">
                        <i class="fa-solid fa-user-large user-icon"></i>
                        <span>{{ $question }}</span>
                    </div>
                </div>
            @endif

            @if(! empty($tableHtml))
                @php $tableRows = $tableRows ?? []; @endphp
                <div class="table-chart-response">
                    {!! $tableHtml !!}
                </div>
            @endif

            {{-- @if(! empty($chartBase64))
                <div class="table-chart-response">
                    <img src="data:image/png;base64,{{ $chartBase64 }}" alt="AI-generated chart"/>
                    <a href="data:image/png;base64,{{ $chartBase64 }}" download="chart.png" class="btn-icon"><i class="fa-regular fa-floppy-disk"></i></a>
                </div>
            @endif --}}

            {{-- <div id="summary-response"></div> --}}
            {{-- @if(isset($summary))
                <div class="user-input">
                    <div class="bubble">
                        <i class="fa-solid fa-robot user-icon"></i>
                        <span>{{ ($summary) }}</span>
                    </div>
                </div>
            @endif --}}

        </div>

        {{-- TIMER --}}
        <div id="timerBox" class="timer-box" style="display:none;">
            <strong>Processing... Elapsed Time: <span id="elapsed">0</span> seconds</strong>
        </div>

        <form id="askForm" class="form-wrapper-chat" method="POST" action="{{ route('mcp.ask') }}">
            @csrf
            <div class="form-group-chat">
                <input type="text" name="question" placeholder="Ask anything...." autocomplete="off" required>
                <button type="submit" class="btn-chat"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </form>

    </div>

</div>

@endsection


@push('scripts')
    <script>hljs.highlightAll();</script>
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

    {{-- @if (!empty($tableRows))
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch("{{ url('/summary') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    question: @json($question),
                    table_rows: @json($tableRows)
                })
            })
            .then(response => response.json())
            .then(data => {
                const summary = data.summary ?? 'No summary could be generated.';
                const bubbleHTML = `
                    <div class="user-input">
                        <div class="bubble">
                            <i class="fa-solid fa-robot user-icon"></i>
                            <span>${summary}</span>
                        </div>
                    </div>`;
                document.getElementById("summary-response").innerHTML = bubbleHTML;
            })
            .catch(err => {
                document.getElementById("summary-response").innerHTML = `
                    <div class="user-input">
                        <div class="bubble">
                            <i class="fa-solid fa-robot user-icon"></i>
                            <span>Error generating summary.</span>
                        </div>
                    </div>`;
                console.error("Summary Error:", err);
            });
        });
    </script>
    @endif --}}
@endpush