@extends('layouts.app')
@section('title', 'AI Agent')

@section('content')

<div class="agent-canvas">

    <div class="left-pannel">

        <div class="database-list">
            <h4>AVAILABLE TABLES</h4>
            @if(!empty($schema))
                <span class="database"><i class="fa-solid fa-database"></i> {{ $schema['database'] ?? 'Database' }}</span>
                <ul>
                    @foreach ($schema['tables'] ?? [] as $table => $columns)
                        <li>
                            <details>
                                <summary>{{ $table }}</summary>
                                <ul>
                                    @foreach ($columns as $column)
                                        <li class="column"><i class="fa-regular fa-circle"></i>{{ $column }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endforeach
                </ul>
            @endif
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

            @if(isset($query))
                <div class="user-input">
                    <div class="bubble">
                        <i class="fa-solid fa-user-large user-icon"></i>
                        <span>{{ $query }}</span>
                    </div>
                </div>
            @endif

            @if(! empty($tableHtml))
                @php $tableRows = $tableRows ?? []; @endphp

                <div class="table-chart-response">
                    {!! $tableHtml !!}

                    @if(!empty($tableRows))
                        <form action="{{ route('ai-agent.download.csv') }}" method="POST">
                            @csrf
                            <input type="hidden" name="table_rows" value='@json($tableRows)'>
                            <button type="submit" class="btn-icon" title="Download as CSV"><i class="fa-regular fa-floppy-disk"></i></button>
                        </form>
                    @endif
                </div>
            @endif

            @if(! empty($chartBase64))
                <div class="table-chart-response">
                    <img src="data:image/png;base64,{{ $chartBase64 }}" alt="AI-generated chart"/>
                    <a href="data:image/png;base64,{{ $chartBase64 }}" download="chart.png" class="btn-icon"><i class="fa-regular fa-floppy-disk"></i></a>
                </div>
            @endif


            @if(isset($summary))
                <div class="user-input">
                    <div class="bubble">
                        <i class="fa-solid fa-robot user-icon"></i>
                        <span>{!! nl2br(e($summary)) !!}</span>
                    </div>
                </div>
            @endif

        </div>

        <div id="timerBox" class="timer-box" style="display:none;">
            <strong>Processing... Elapsed Time: <span id="elapsed">0</span> seconds</strong>
        </div>
        <form id="askForm" class="form-wrapper-chat" method="POST" action="{{ route('ai-agent.ask') }}">
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

@endpush


