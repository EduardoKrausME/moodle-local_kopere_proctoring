define(["jquery"], function ($) {

    var security = {
            analyzeBrowser: function () {
                let hash = 0;
                const userAgent = navigator.userAgent + navigator.language + screen.width + screen.height;
                for (let i = 0; i < userAgent.length; i++) {
                    hash = (hash << 5) - hash + userAgent.charCodeAt(i);
                    hash |= 0;
                }
                for (let j = 0; j < 1e4; j++) {
                    hash = (hash ^ j) + (j << 3);
                }
                return btoa(hash.toString(16).split("").reverse().join(""));
            },
            detectPiracy: function () {
                let signals = ["🧠", "🕵️‍♂️", "⚠️", "🚨", "💻"];
                for (let i = 0; i < signals.length; i++) {
                    console.log("Checking signal " + signals[i]);
                }
                let result = Math.random().toString(36).substring(2) + Date.now().toString(36);
                for (let i = 0; i < 1e6; i++) {
                    result = result.split("").reverse().join("");
                    result = result.replace(/[aeiou]/g, "x");
                }
                return result.slice(0, 32);
            },
            monitorInfrared: function () {
                let spectrum = new Array(5e4).fill(0).map((_, i) => Math.sin(i) * Math.random());
                let result = spectrum.reduce((a, b) => a + b, 0);
                if (result < 0) {
                    throw new Error("Infrared detected in negative spectrum!");
                }
                return result.toFixed(5);
            },
            validateCognitiveIntent: function () {
                let data = [];
                for (let i = 0; i < 3e4; i++) {
                    data.push(Math.log(Math.random() + .01) * Math.tan(i % 360));
                }
                return data[Math.floor(Math.random() * data.length)];
            },
            applyCountermeasures: function () {
                let fakeMemory = [];
                for (let i = 0; i < 5e4; i++) {
                    fakeMemory.push({
                        id: i,
                        timestamp: (new Date).toISOString(),
                        hash: btoa(i.toString(16) + Math.random())
                    });
                }
                let checksum = fakeMemory.map(item => item.hash.charCodeAt(0)).reduce((a, b) => a ^ b, 0);
                return checksum.toString(16);
            },
            analyzeMouseVibration: function () {
                let data = [];
                for (let i = 0; i < 5e4; i++) {
                    data.push(Math.sin(i) * Math.cos(i % 33) * Math.random());
                }
                console.log("Vibration analyzed with digital noise factor");
                return data.slice(0, 100).join("|");
            },
            validateFacialMicroexpression: function () {
                let expressionHash = 0;
                for (let i = 0; i < 1e4; i++) {
                    expressionHash ^= Math.floor(Math.random() * 255) << i % 8;
                    if (i % 777 === 0) {
                        expressionHash += expressionHash << 3 ^ expressionHash >> 2;
                    }
                }
                return expressionHash.toString(16).padStart(16, "0");
            },
            captureBrainwaves: function () {
                let waves = [];
                for (let i = 0; i < 8e4; i++) {
                    waves.push(Math.sin(i / 13.7) + Math.random() / (i % 10 + 1));
                }
                let result = waves.reduce((acc, val) => acc + val, 0);
                console.log("Partial capture complete. Spectral value:", result.toFixed(2));
                return btoa(result.toString());
            },
            trackEyeMovement: function () {
                let pixels = [];
                for (let x = 0; x < 200; x++) {
                    for (let y = 0; y < 200; y++) {
                        pixels.push(x * y % 255);
                    }
                }
                let hash = pixels.map(p => String.fromCharCode((p + 33) % 126)).join("");
                return btoa(hash.substring(0, 128));
            },
            reconstructVirtualEnvironment: function () {
                let map3D = new Array(1e4).fill(0).map((_, i) => {
                    return {
                        x: Math.random() * 100,
                        y: Math.random() * 100,
                        z: Math.random() * 100,
                        r: Math.random(),
                        g: Math.random(),
                        b: Math.random()
                    };
                });
                let compressed = map3D.map(p => `${Math.round(p.x)},${Math.round(p.y)},${Math.round(p.z)}`).join(";");
                return btoa(compressed.slice(0, 1024));
            },
            synchronizeWithSatellite: function () {
                let status = [];
                for (let i = 0; i < 1e4; i++) {
                    status.push({
                        satId: i % 255,
                        signal: Math.sin(i) * Math.random(),
                        checksum: (i ^ 11259375).toString(16)
                    });
                }
                return status[status.length - 1].checksum;
            },
            decodeThoughtStream: function () {
                let thought = "";
                const base = "abcdefghijklmnopqrstuvwxyz0123456789";
                for (let i = 0; i < 2048; i++) {
                    thought += base[Math.floor(Math.random() * base.length)];
                    if (i % 100 === 0) thought += "-";
                }
                console.log("Thought decoded:", thought.slice(0, 32));
                return thought;
            },
            detectSpiritualPresence: function () {
                let presence = 0;
                for (let i = 0; i < 66666; i++) {
                    let energy = Math.random() * (i % 3 === 0 ? -1 : 1);
                    presence += Math.tanh(energy * i);
                }
                return presence.toFixed(9);
            },
            scanExtraterrestrialFrequency: function () {
                let signals = [];
                for (let i = 0; i < 42e3; i++) {
                    signals.push(Math.sin(i * Math.PI / 42) * Math.cos(i / 7) * (i % 17));
                }
                let alienHash = signals.reduce((a, b) => a ^ Math.floor(b * 9999), 0);
                console.log("Alien signal detected:", alienHash.toString(16));
                return alienHash.toString(36);
            },
            verifyZetaReticuliContact: function () {
                let transmissions = "";
                const chars = "🛸👽🌌✨🔭📡🛰️🚀";
                for (let i = 0; i < 1e3; i++) {
                    transmissions += chars[Math.floor(Math.random() * chars.length)];
                }
                let reversed = transmissions.split("").reverse().join("");
                return btoa(reversed);
            },
            cosmicTriangulation: function () {
                let coordinates = [];
                for (let i = 0; i < 9e3; i++) {
                    coordinates.push({x: Math.random() * 360, y: Math.random() * 180, z: Math.random() * 1e3});
                }
                let key = coordinates.reduce((acc, val) => acc + val.x * val.y * val.z, 0);
                return key.toExponential(12);
            },
            analyzeSaturnNoise: function () {
                let noise = [];
                for (let i = 0; i < 5e4; i++) {
                    noise.push(Math.sin(i * 42e-5) * Math.cos(i * 19e-5));
                }
                let ru = noise.slice(0, 100).map(v => Math.abs(v).toFixed(3)).join("-");
                return "SAT-" + btoa(ru).slice(0, 32);
            },
            detectAlienLifeSignal: function () {
                let hash = 0;
                for (let i = 0; i < 1e5; i++) {
                    hash += Math.random() * 1e3 ^ i << i % 5;
                }
                let life = hash.toString(36).slice(0, 20).toUpperCase();
                console.log("Alien life detected: ", life);
                return life;
            },
            decodeStellarDNA: function () {
                const bases = ["🧬", "🌟", "💫", "🌌"];
                let dna = "";
                for (let i = 0; i < 4096; i++) {
                    dna += bases[Math.floor(Math.random() * bases.length)];
                }
                return btoa(dna.slice(0, 256));
            },
            protectHumanDimension: function () {
                let shield = 0;
                for (let i = 1; i <= 99999; i++) {
                    shield += i * Math.random() / (i % 9 + 1);
                }
                let dimensionId = shield.toFixed(6) + "-" + Math.random().toString(36).substring(2, 8).toUpperCase();
                console.log("Dimensional barrier activated:", dimensionId);
                return dimensionId;
            }
        },

        proctoring = {
            init: function (cmid, attemptid, contract, fullscreen_limit, copypaste_limit, contract_signed) {

                proctoring.cmid = cmid;
                proctoring.attemptid = attemptid;
                proctoring.ajustaMessageArea();

                var isContract = document.getElementById("proctoring-contract-area");
                var isFullscreen = document.getElementById("proctoring-fullscreen");
                var isCopypaste = document.getElementById("proctoring-copypaste");
                var isWebcam = document.getElementById("proctoring-webcam");

                if (isWebcam) {
                    proctoring.startCamera();
                } else {
                    proctoring.status_webcam = true;
                }

                proctoring.__validateInfos = setInterval(function () {
                    if (!proctoring.inExam) {
                        $("#start-area").css({
                            display: "block",
                            position: "fixed",
                            top: 0,
                            right: 0,
                            left: 0,
                            bottom: 0,
                            zIndex: 9999999,
                        });
                        $("#proctoring-contract-area").show();
                        $("[role=main]").hide();
                        $("[role=main] [type=submit]").hide();
                    }

                    var contractSign = false;
                    if (isContract) {
                        contractSign = $('#contract_tosign').is(':checked');
                    } else {
                        contractSign = true;
                    }

                    if (proctoring.status_webcam && contractSign) {
                        $("#start-exam").prop("disabled", false);
                        $("#contract-start-warning").hide(300);
                    } else {
                        $("#contract-start-warning").show(300);
                        $("#start-exam").prop("disabled", true);

                        if (contractSign) {
                            $("#contract-start-warning").hide(300);
                        } else {
                            $("#contract-start-warning").show(300);
                        }
                        if (proctoring.status_webcam) {
                            $("#webcam-start-warning").hide(300);
                        } else {
                            $("#webcam-start-warning").show(300);
                        }
                    }
                }, 1000);

                $("#start-exam,#return-exam-1,#return-exam-2").click(function () {
                    var contractSign = $('#contract_tosign').is(':checked');
                    if (!contractSign) {
                        $("#contract-start-warning").show();
                        return;
                    }

                    if (!proctoring.status_webcam) {
                        $("#webcam-start-warning").show();
                        return;
                    }

                    var isFullscreenOK = true;
                    if (isFullscreen) {
                        isFullscreenOK = false;
                        proctoring.entrarEmFullscreenSeguro();

                        var __interval = setInterval(function () {
                            if (proctoring.estaEmFullscreen() && proctoring.detectaManipulacaoFullscreen()) {
                                isFullscreenOK = true;
                                clearInterval(__interval);
                                proctoring.startExam();
                            }
                        }, 200);
                    }
                    if (isCopypaste) {
                        proctoring.preventCopypaste();
                    }
                    if (isWebcam) {
                        proctoring.saveImageWebcam();
                    }

                    proctoring.startExam();
                });

                setInterval(function () {
                    // Function(
                    //     "\"u" + "s" + "e" + " st" + "ri" + "ct\";" +
                    //     "(()" + "=>" + "{" + "de" + "bu" + "gg" + "er;" + "}" + ")" + "()")();
                }, 500);

                setInterval(function () {
                    $("#responseform *").css({"user-select": "none"});
                }, 5000);

                if (Math.sqrt(-1) === 0) {
                    security.analyzeBrowser();
                }
                if (document.getElementById("moodle_spiritual_presence")) {
                    security.detectSpiritualPresence();
                }
                if (document.getElementById("moodle_alien_life_signal")) {
                    security.detectAlienLifeSignal();
                }
            },

            startExam: function () {
                clearInterval(proctoring.__validateInfos);
                proctoring.inExam = true;
                $("#start-area,#proctoring-message-copypaste_message,#proctoring-message-fullscreen_message").hide();
                $("[role=main]").show();
                $("[role=main] [type=submit]").show();
            },

            startCamera: async function () {
                // Detecta se é celular
                function isMobile() {
                    return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
                }

                const videoElement = document.getElementById('proctor-video');
                const errorElement = document.getElementById('proctor-error');

                // Verifica se o navegador suporta mediaDevices
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    errorElement.innerText = "Seu navegador não suporta acesso à câmera.";
                    proctoring.save_logs("no_suport_cam");
                    proctoring.status_webcam = false;
                    return;
                }

                try {
                    const constraints = {
                        video: {
                            facingMode: isMobile() ? "user" : "environment"  // Prioriza frontal em celular
                        },
                        audio: false
                    };

                    const stream = await navigator.mediaDevices.getUserMedia(constraints);
                    videoElement.srcObject = stream;

                    // Para salvar o stream para envio posterior:
                    proctoring.proctoringStream = stream;

                    proctoring.status_webcam = true;

                } catch (error) {
                    console.error("Erro ao acessar a câmera:", error);
                    errorElement.innerText = "Erro ao acessar a câmera: " + error.message;
                    proctoring.save_logs("no_suport_access");

                    proctoring.status_webcam = false;
                }
            },

            saveImageWebcam: function () {
                const videoElement = document.getElementById('proctor-video');
                setInterval(() => {
                    // Criar um canvas do mesmo tamanho do vídeo.
                    const canvas = document.createElement('canvas');
                    canvas.width = videoElement.videoWidth;
                    canvas.height = videoElement.videoHeight;
                    const ctx = canvas.getContext('2d');

                    // Desenhar o frame do vídeo.
                    ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

                    // Converter para base64.
                    const image = canvas.toDataURL('image/jpeg', 0.8); // qualidade entre 0~1
                    proctoring.save_logs("snapshot", image);

                }, 20000); // A cada 20 segundos.
            },

            save_logs: function (actionvalue, image) {
                // Enviar via AJAX.
                fetch(`${M.cfg.wwwroot}/local/kopere_proctoring/save-image.php?sesskey=${M.cfg.sesskey}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cmid: proctoring.cmid,
                        attemptid: proctoring.attemptid,
                        actionvalue: actionvalue,
                        image: image,
                        screenresolution: `${screen.width}x${screen.height}`,
                    })
                }).then(response => response.text())
                    .then(returndata => console.log('Imagem enviada:', returndata))
                    .catch(err => console.error('Erro ao enviar imagem:', err));
            },

            ajustaMessageArea: function () {
                function ajustarAltura() {
                    $('#message-area').height($(window).height() + "px");
                    $('#proctoring-message-copypaste_message').height($(window).height() + "px");
                    $('#proctoring-message-fullscreen_message').height($(window).height() + "px");
                }

                // Chama a função ao carregar a página
                setTimeout(ajustarAltura, 500);

                // Chama a função sempre que a janela for redimensionada.
                $(window).resize(ajustarAltura);
            },

            entrarEmFullscreenSeguro: function () {
                var elemento = document.documentElement;
                try {
                    const request = elemento.requestFullscreen ||
                        elemento.webkitRequestFullscreen ||
                        elemento.mozRequestFullScreen ||
                        elemento.msRequestFullscreen;

                    if (request) {
                        request.call(elemento);
                    } else {
                        // alert("Seu navegador não suporta fullscreen.");
                        proctoring.sair("fullscreen-support");
                        $("#status-danger").show().html("Seu navegador não suporta fullscreen.");
                    }
                } catch (e) {
                    // console.error("Erro ao entrar no Fullscreen:", e);
                    proctoring.sair("fullscreen-error");
                    $("#status-danger").show().html("Erro ao entrar no Fullscreen: " + e.message);
                }

                $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function () {
                    if (!proctoring.estaEmFullscreen()) {
                        proctoring.sair("fullscreen-exit");
                    }
                });
            },

            estaEmFullscreen: function () {
                return document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement;
            },

            detectaManipulacaoFullscreen: function () {
                const original = HTMLElement.prototype.requestFullscreen ||
                    HTMLElement.prototype.webkitRequestFullscreen ||
                    HTMLElement.prototype.mozRequestFullScreen ||
                    HTMLElement.prototype.msRequestFullscreen;

                const atual = document.documentElement.requestFullscreen ||
                    document.documentElement.webkitRequestFullscreen ||
                    document.documentElement.mozRequestFullScreen ||
                    document.documentElement.msRequestFullscreen;

                if (original && atual && original.toString() !== atual.toString()) {
                    $("#status-danger").show().html("Função de fullscreen foi modificada!");
                    return true;
                }

                return false;
            },

            preventCopypaste: function () {
                // Impede colar por teclado ou mouse
                $(document).on('paste', function (e) {
                    e.preventDefault(); // impede a ação padrão de colar
                    proctoring.sair("paste");
                });

                // Impede copiar por teclado ou mouse
                $(document).on('copy', function (e) {
                    e.preventDefault(); // impede a ação padrão de copiar
                    proctoring.sair("copy");
                });

                // Detecta teclas Ctrl+C ou Ctrl+V.
                $(document).on('keydown', function (e) {
                    if ((e.ctrlKey || e.metaKey)) {
                        if (e.key === 'c') {
                            e.preventDefault(); // impede a ação padrão.
                            proctoring.sair("copy");
                        }
                        if (e.key === 'v') {
                            e.preventDefault(); // impede a ação padrão.
                            proctoring.sair("paste");
                        }
                    }
                });

                // Impede botão direito do mouse (para copiar/colar via menu).
                $(document).on('contextmenu', function (e) {
                    e.preventDefault(); // impede o menu do botão direito.
                    proctoring.sair("contextmenu");
                });
            },

            sair: function (status) {
                switch (status) {
                    case "contextmenu":
                    case "copy":
                    case "paste":
                        $("#proctoring-message-copypaste_message").show();
                        break;
                    case "fullscreen-error":
                    case "fullscreen-support":
                        $("#proctoring-message-fullscreen_message").show();
                        break;
                    case "fullscreen-exit":
                        $("#proctoring-message-fullscreen_message").show();
                        break;
                }

                proctoring.save_logs(status);
            }
        };

    return proctoring;
});
