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
        /* display: flex; */
        /* align-items: center; */
        justify-content: center;
        /* position: absolute; */
        /* top: 50%; */
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-auto" style="display: flex; align-items: center;">
            <div class="kasir">
                KASIR
            </div>
        </div>
        <div class="col">
            <div class="row">
                <!-- First row of tables -->
                <!-- <div class="col-2"></div> -->
                @for ($i = 0; $i < 3; $i++)
                    <div class="col-2 col-lg-3">
                        @foreach($meja_rental as $index => $mi)
                            @if($index == $i)
                                <div class="card">
                                    <a href="#" class="menu-link" data-nomor-meja="{{ $mi['nomor_meja'] }}" data-status="{{ $mi['status'] }}">
                                        <div class="card-body">
                                            <div class="meja {{ $mi['status'] === 'lanjut' ? 'meja-yellow' : ($mi['waktu_akhir'] ? 'meja-yellow' : 'meja-green') }}" 
                                                data-end-time="{{ $mi['waktu_akhir'] }}" 
                                                data-start-time="{{ $mi['waktu_mulai'] }}" 
                                                data-nomor-meja="{{ $mi['nomor_meja'] }}">
                                                Meja {{ $mi['nomor_meja'] }}
                                            </div>
                                            <div class="{{ $mi['status'] === 'lanjut' ? 'stopwatch' : 'countdown' }}" data-status="{{ $mi['status'] }}">
                                                {{ $mi['status'] === 'lanjut' ? '00:00:00' : ($mi['waktu_akhir'] ?? 'N/A') }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endfor
            </div>

            <div class="row">
                <!-- <div class="col-2"></div> -->
                @for ($i = 3; $i < 7; $i++)
                    <div class="col-2 col-lg-3">
                        @foreach($meja_rental as $index => $mi)
                            @if($index == $i)
                                <div class="card">
                                    <a href="#" class="menu-link" data-nomor-meja="{{ $mi['nomor_meja'] }}" data-status="{{ $mi['status'] }}">
                                        <div class="card-body">
                                            <div class="meja {{ $mi['status'] === 'lanjut' ? 'meja-yellow' : ($mi['waktu_akhir'] ? 'meja-yellow' : 'meja-green') }}" 
                                                data-end-time="{{ $mi['waktu_akhir'] }}" 
                                                data-start-time="{{ $mi['waktu_mulai'] }}" 
                                                data-nomor-meja="{{ $mi['nomor_meja'] }}">
                                                Meja {{ $mi['nomor_meja'] }}
                                            </div>
                                            <div class="{{ $mi['status'] === 'lanjut' ? 'stopwatch' : 'countdown' }}" data-status="{{ $mi['status'] }}">
                                                {{ $mi['status'] === 'lanjut' ? '00:00:00' : ($mi['waktu_akhir'] ?? 'N/A') }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endfor
            </div>

            <div class="divider"></div>

            <div class="row">
                @for ($i = 7; $i < 15; $i++)
                    <div class="col-2 col-lg-3">
                    @foreach($meja_rental as $index => $mi)
                        @if($index == $i)
                            <div class="card">
                                <a href="#" class="menu-link" data-nomor-meja="{{ $mi['nomor_meja'] }}" data-status="{{ $mi['status'] }}">
                                    <div class="card-body">
                                        <div class="meja {{ $mi['status'] === 'lanjut' ? 'meja-yellow' : ($mi['waktu_akhir'] ? 'meja-yellow' : 'meja-green') }}" 
                                            data-end-time="{{ $mi['waktu_akhir'] }}" 
                                            data-start-time="{{ $mi['waktu_mulai'] }}" 
                                            data-nomor-meja="{{ $mi['nomor_meja'] }}">
                                            Meja {{ $mi['nomor_meja'] }}
                                        </div>
                                        <div class="{{ $mi['status'] === 'lanjut' ? 'stopwatch' : 'countdown' }}" data-status="{{ $mi['status'] }}">
                                            {{ $mi['status'] === 'lanjut' ? '00:00:00' : ($mi['waktu_akhir'] ?? 'N/A') }}
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

                if (status === 'lanjut') {
                    // Get the current stopwatch time from the displayed text
                    lamaMain = this.querySelector('.stopwatch').innerText;
                }

                // Redirect to the menu page with nomor_meja and lama_main as query parameters
                window.location.href = `/bl/menu/${nomorMeja}?lama_main=${lamaMain}`;
            });
        });
    });

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

    // function startStopwatch(element, startTime) {
    //     const stopwatchKey = `stopwatch_${element.closest('.card-body').querySelector('.meja').getAttribute('data-nomor-meja')}`;
    //     const start = startTime || new Date().getTime();

    //     if (!startTime) {
    //         localStorage.setItem(stopwatchKey, start);
    //     }

    //     element.querySelector('.meja').classList.remove('meja-green');
    //     element.querySelector('.meja').classList.add('meja-yellow');

    //     function updateStopwatch() {
    //         const now = new Date().getTime();
    //         const elapsed = now - start;

    //         const hours = Math.floor((elapsed % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    //         const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
    //         const seconds = Math.floor((elapsed % (1000 * 60)) / 1000);

    //         element.querySelector('.stopwatch').innerHTML =
    //             `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    //     }

    //     updateStopwatch();
    //     setInterval(updateStopwatch, 1000);
    // }
    function startStopwatch(element, startTime) {
    const stopwatchKey = `stopwatch_${element.closest('.card-body').querySelector('.meja').getAttribute('data-nomor-meja')}`;
    const start = startTime ? new Date(startTime).getTime() : new Date().getTime(); // Use database startTime if available

    if (!startTime) {
        localStorage.setItem(stopwatchKey, start);
    }

    element.querySelector('.meja').classList.remove('meja-green');
    element.querySelector('.meja').classList.add('meja-yellow');

    function updateStopwatch() {
        const now = new Date().getTime();
        const elapsed = now - start; // Calculate elapsed time from start time

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

    // document.addEventListener('DOMContentLoaded', function () {
    //     const timerElements = document.querySelectorAll('.countdown, .stopwatch');
    //     timerElements.forEach(element => {
    //         const status = element.getAttribute('data-status');
    //         const nomorMeja = element.closest('.card-body').querySelector('.meja').getAttribute('data-nomor-meja');

    //         if (status === 'lanjut') {
    //             const stopwatchKey = `stopwatch_${nomorMeja}`;
    //             const startTime = localStorage.getItem(stopwatchKey);
    //             startStopwatch(element.closest('.card-body'), startTime);
    //         } else {
    //             const endTimeString = element.closest('.card-body').querySelector('.meja').getAttribute('data-end-time');
    //             if (endTimeString) {
    //                 startCountdown(element.closest('.card-body'), endTimeString);
    //             }
    //         }
    //     });
    // });
    document.addEventListener('DOMContentLoaded', function () {
    const timerElements = document.querySelectorAll('.countdown, .stopwatch');
    timerElements.forEach(element => {
        const status = element.getAttribute('data-status');
        const nomorMeja = element.closest('.card-body').querySelector('.meja').getAttribute('data-nomor-meja');

        if (status === 'lanjut') {
            const startTime = element.closest('.card-body').querySelector('.meja').getAttribute('data-start-time');
            startStopwatch(element.closest('.card-body'), startTime); // Pass the database start time to the stopwatch
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
