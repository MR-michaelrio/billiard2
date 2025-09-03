@extends('layout.main')
@section('content')
<style>
    .meja {
        color: black;
        width: 100%;
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

    .divider {
        height: 20px;
        background-color: black;
        margin: 20px 0;
    }

    .kasir {
        writing-mode: vertical-rl;
        text-align: center;
        background-color: #5f5f5f;
        color: white;
        padding: 20px;
        font-size: 24px;
        padding:100px 10px;
        justify-content: center;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="row">
                @for ($i = 0; $i < 12; $i++)
                    <div class="col-2 col-lg-3">
                        @foreach($meja_rental as $index => $mi)
                            @if($index == $i)
                                <div class="card">
                                    <a href="#" class="menu-link" 
                                       data-nomor-meja="{{ $mi['nomor_meja'] }}" 
                                       data-status="{{ $mi['status'] ?? 'kosong' }}">
                                        <div class="card-body">
                                            <div class="meja {{ $mi['status'] === 'lanjut' ? 'meja-yellow' : ($mi['waktu_akhir'] ? 'meja-yellow' : 'meja-green') }}" 
                                                 data-end-time="{{ $mi['waktu_akhir'] }}" 
                                                 data-start-time="{{ $mi['waktu_mulai'] }}" 
                                                 data-nomor-meja="{{ $mi['nomor_meja'] }}">
                                                Meja {{ $mi['nomor_meja'] }}
                                            </div>
                                            <div class="{{ in_array($mi['status'], ['lanjut','tambahanlanjut']) ? 'stopwatch' : 'countdown' }}" 
                                                 data-status="{{ $mi['status'] ?? 'kosong' }}">
                                                @if($mi['status'] === 'lanjut' || $mi['status'] === 'tambahanlanjut')
                                                    00:00:00
                                                @elseif($mi['status'] === 'tambahan')
                                                    {{ $mi['waktu_akhir'] ?? 'N/A' }}
                                                @elseif($mi['status'] === 'selesai')
                                                    00:00:00
                                                @elseif(empty($mi['status']) || $mi['status'] === 'kosong')
                                                    {{ $mi['waktu_akhir'] ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const links = document.querySelectorAll('.menu-link');

        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();

                const nomorMeja = this.getAttribute('data-nomor-meja');
                const status = this.getAttribute('data-status');
                let lamaMain = '00:00:00';

                if (status === 'lanjut' || status === 'tambahanlanjut') {
                    lamaMain = this.querySelector('.stopwatch').innerText;
                }

                window.location.href = `/bl/menu/${nomorMeja}?lama_main=${lamaMain}`;
            });
        });
    });

    // --- COUNTDOWN ---
    function startCountdown(element, endTime) {
    function parseDateTime(dateTimeStr) {
        if (!dateTimeStr) return new Date();
        return new Date(dateTimeStr.replace(' ', 'T'));
    }

    // Tambahkan class kuning saat countdown dimulai
    element.querySelector('.meja').classList.remove('meja-green', 'meja-red');
    element.querySelector('.meja').classList.add('meja-yellow');

    function updateCountdown() {
        const now = new Date().getTime();
        const endTimeMillis = parseDateTime(endTime).getTime();
        const timeRemaining = endTimeMillis - now;

        if (timeRemaining <= 0) {
            element.querySelector('.countdown').innerHTML = "00:00:00";
            element.querySelector('.meja').classList.remove('meja-green', 'meja-yellow');
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

    // --- STOPWATCH ---
    function startStopwatch(element, startTime) {
        const start = startTime ? new Date(startTime.replace(' ', 'T')).getTime() : new Date().getTime();
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

    // --- RESET STOPWATCH ---
    function resetStopwatch(noMeja) {
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

    // --- INIT TIMER BERDASARKAN STATUS ---
    document.addEventListener('DOMContentLoaded', function () {
        const timerElements = document.querySelectorAll('.countdown, .stopwatch');

        timerElements.forEach(element => {
            const status = element.getAttribute('data-status') || 'kosong';
            const cardBody = element.closest('.card-body');
            const meja = cardBody.querySelector('.meja');
            const startTime = meja.getAttribute('data-start-time');
            const endTimeString = meja.getAttribute('data-end-time');
            console.log("endTimeString",endTimeString);
            meja.classList.remove('meja-green', 'meja-yellow', 'meja-red');

            switch (status) {
                case 'baru':
                    if (endTimeString) {
                        startCountdown(cardBody, endTimeString);
                    } else {
                        element.innerHTML = "00:00:00";
                        meja.classList.add('meja-green');
                    }
                    break;

                case 'lanjut':
                    startStopwatch(cardBody, startTime);
                    break;

                case 'tambahan':
                    if (endTimeString) {
                        startCountdown(cardBody, endTimeString);
                    }
                    break;

                case 'selesai':
                    element.innerHTML = "00:00:00";
                    meja.classList.add('meja-red');
                    break;

                case 'tambahanlanjut':
                    startStopwatch(cardBody, startTime);
                    break;

                default:
                    element.innerHTML = "N/A";
                    meja.classList.add('meja-green');
            }
        });
    });

    // --- HANDLE RESPONSE ---
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
