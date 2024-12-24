@extends('layout.main')
@section('content')
<style>
    .meja {
        color: black;
        width: auto;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
    }

    .meja-green {
        background-color: #72fc89;
    }

    .meja-yellow {
        background-color: #ffd666;
    }

    .meja-red {
        background-color: #ff6666;
    }

    .countdown, .stopwatch {
        font-weight: bold;
        color: red;
        text-align: center;
        font-size: 24px;
    }

    .card {
        margin-bottom: 20px;
    }
</style>
<div class="row">
    @foreach($meja_rental as $mi)
    <div class="col-12 col-md-4 col-lg-3">
        <div class="card">
            <a href="{{ route('bl.menu', $mi['nomor_meja']) }}">
                <div class="card-body">
                    <div class="meja {{ $mi['status'] === 'lanjut' ? 'meja-yellow' : ($mi['waktu_akhir'] ? 'meja-yellow' : 'meja-green') }}" data-end-time="{{ $mi['waktu_akhir'] }}" data-nomor-meja="{{ $mi['nomor_meja'] }}">
                        Meja {{ $mi['nomor_meja'] }}
                    </div>
                    <div class="{{ $mi['status'] === 'lanjut' ? 'stopwatch' : 'countdown' }}" data-status="{{ $mi['status'] }}">
                        {{ $mi['status'] === 'lanjut' ? '00:00:00' : ($mi['waktu_akhir'] ?? 'N/A') }}
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endforeach
</div>

<script>
    function startCountdown(element, endTime) {
        function updateCountdown() {
            const now = new Date().getTime();
            const endTimeMillis = new Date(endTime).getTime();
            const timeRemaining = endTimeMillis - now;

            if (timeRemaining <= 0) {
                element.querySelector('.countdown').innerHTML = "00:00:00";
                element.querySelector('.meja').classList.remove('meja-yellow');
                element.querySelector('.meja').classList.add('meja-red');
                return;
            }

            const hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);

            element.querySelector('.countdown').innerHTML =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    function startStopwatch(element, startTime) {
        const stopwatchKey = `stopwatch_${element.closest('.card-body').querySelector('.meja').getAttribute('data-nomor-meja')}`;
        const start = startTime || new Date().getTime();

        if (!startTime) {
            localStorage.setItem(stopwatchKey, start);
        }

        element.querySelector('.meja').classList.remove('meja-green');
        element.querySelector('.meja').classList.add('meja-yellow');

        function updateStopwatch() {
            const now = new Date().getTime();
            const elapsed = now - start;

            const hours = Math.floor((elapsed % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);

            element.querySelector('.stopwatch').innerHTML =
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        updateStopwatch();
        setInterval(updateStopwatch, 1000);
    }

    function resetStopwatch(noMeja) {
        const stopwatchKey = `stopwatch_${noMeja}`;
        localStorage.removeItem(stopwatchKey);

        const element = document.querySelector(`.meja[data-nomor-meja="${noMeja}"]`);
        if (element) {
            const stopwatchElement = element.closest('.card-body').querySelector('.stopwatch');
            if (stopwatchElement) {
                stopwatchElement.innerHTML = '00:00:00';
            }
            element.classList.remove('meja-yellow', 'meja-red');
            element.classList.add('meja-green');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const timerElements = document.querySelectorAll('.countdown, .stopwatch');
        timerElements.forEach(element => {
            const status = element.getAttribute('data-status');
            const nomorMeja = element.closest('.card-body').querySelector('.meja').getAttribute('data-nomor-meja');

            if (status === 'lanjut') {
                const stopwatchKey = `stopwatch_${nomorMeja}`;
                const startTime = localStorage.getItem(stopwatchKey);
                startStopwatch(element.closest('.card-body'), startTime);
            } else {
                const endTimeString = element.closest('.card-body').querySelector('.meja').getAttribute('data-end-time');
                if (endTimeString) {
                    startCountdown(element.closest('.card-body'), endTimeString);
                }
            }
        });
    });

    function handleSuccessResponse(data) {
        if (data.success) {
            resetStopwatch(data.no_meja);
            showAlert('Success','Order submitted successfully','success');
            window.location.href = '{{ route("bl.index") }}';
        } else {
            showAlert('Error','There was an error submitting the order','error');
        }
    }
</script>
@endsection
