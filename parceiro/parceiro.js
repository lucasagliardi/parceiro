// SERVIÇOS QUE CHAMAM O BACK-END
app.factory("parceiroService", ['$http', function ($http) {
    var serviceBase = 'service/parceiro/';
    var obj = {};
    // CHAMADA SERVICO DE LISTAGEM
    obj.getFormaPagamento = function () {
        return $http.get(serviceBase + 'formaPagamento');
    }
    // CHAMADA SERVICO DE PARCEIRO ONLINE
    obj.parceiroOnline = function (obj) {
        return $http.get(serviceBase + 'parceiroOnline?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa + '&latitude=' + obj.parceiro.latitude + '&longitude=' + obj.parceiro.longitude);
    };
    // CHAMADA SERVICO DE PARCEIRO OFFLINE
    obj.parceiroOffline = function (obj) {
        return $http.get(serviceBase + 'parceiroOffline?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa);
    };
    obj.cancelarViagem = function (obj) {
        return $http.get(serviceBase + 'cancelarViagem?id=' + obj.id + '&placa=' + obj.placa);
    }
    // CHAMADA SERVICO DE BUSCAR
    obj.buscarViagem = function (obj) {
        return $http.get(serviceBase + 'buscarViagem?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa + '&latitude=' + obj.parceiro.latitude + '&longitude=' + obj.parceiro.longitude);
    };
    obj.statusViagem = function (obj) {
        return $http.get(serviceBase + 'statusViagem?id=' + obj.dadosAceitacao.idSolicitacao + '&latitude=' + obj.parceiro.latitude + '&longitude=' + obj.parceiro.longitude);
    };
    obj.avaliarUsuario = function (obj) {
        return $http.get(serviceBase + 'avaliarUsuario?id=' + obj.idSolicitacao + '&nota=' + obj.nota);
    };
    // CHAMADA SERVICO DE ACEITAR
    obj.aceitar = function (obj) {
        return $http.get(serviceBase + 'aceitar?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa);
    };
    // CHAMADA SERVICO DE RECUSAR
    obj.recusar = function (obj) {
        return $http.get(serviceBase + 'recusar?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa);
    };
    // CHAMADA SERVICO DE RECUSAR
    obj.iniciar = function (obj) {
        return $http.get(serviceBase + 'iniciar?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa);
    };
    obj.avisoChegada = function (obj) {
        return $http.get(serviceBase + 'avisoChegada?id=' + obj.parceiro.id + '&placa=' + obj.parceiro.placa);
    };
    // CHAMADA SERVICO DE ENCERRAR
    obj.finalizar = function (obj) {
        return $http.get(serviceBase + 'finalizar?id=' + obj.id + '&placa=' + obj.placa + '&formaPagamento=' + obj.formaPagamento);
    };

    return obj;
}]);
// CONTROLER DE LISTAGEM
app.controller('parceiroCtrl', ['$scope', 'ngAudio', 'parceiroService', '$interval', 'loginService',
    function ($scope, ngAudio, parceiroService, $interval, loginService) {
        $scope.audioOnline = ngAudio.load('assets/audio/v8.mp3');
        $scope.audioOffline = ngAudio.load('assets/audio/off.mp3');
        $scope.audioChegadaViagem = ngAudio.load('assets/audio/chegadaViagem.mp3');
        $scope.audioCancelado = ngAudio.load('assets/audio/cancelado.mp3');
        loginService.get('session').then(function (results) {
            if (results.id) {
                $scope.SecaoParceiro = {
                    id: results.id,
                    nome: results.nome,
                    placa: results.placa,
                    email: results.email
                };
                $scope.parceiro = {};

                $scope.parceiro.id = $scope.SecaoParceiro.id;
                $scope.parceiro.placa = $scope.SecaoParceiro.placa;
                $scope.parceiro.nome = $scope.SecaoParceiro.nome;
                $scope.parceiro.cidadeAtual = 'Porto Alegre';
                $scope.inciar = false;
                $scope.iniciou = false;
                function activate() {
                    if ($scope.parceiro.online) {
                        parceiroService.parceiroOnline($scope).then(function () { });
                    } else {
                        parceiroService.parceiroOffline($scope).then(function () { });
                    }
                }
                activate();
                $scope.onlineOffline = function (status) {
                    if (window.navigator && window.navigator.geolocation) {
                        var geolocation = window.navigator.geolocation;
                        geolocation.getCurrentPosition(sucesso, erro);
                    } else {
                        alert('Geolocalização não suportada em seu navegador.')
                    }
                    function sucesso(posicao) {
                        console.log(posicao);
                        $scope.parceiro.latitude = posicao.coords.latitude;
                        $scope.parceiro.longitude = posicao.coords.longitude;
                        if (status) {
                            $scope.audioOnline.play();
                            parceiroService.parceiroOnline($scope).then(function (response) {
                                $scope.idStatusOnline = response.data[0].idResult;
                                $scope.erroStatus = response.data[0].result;
                                if ($scope.idStatusOnline != 9) {
                                    $scope.parceiro.online = true;
                                    $scope.buscarViagem();
                                } else {
                                    $scope.parceiro.online = false;
                                    $scope.loading = false;
                                }
                            });
                        } else {
                            $scope.audioOffline.play();
                            parceiroService.parceiroOffline($scope).then(function () { });
                            $scope.parceiro.online = false;
                            $scope.killtimer();
                        }
                    }
                    function erro(error) {
                        console.log(error)
                    }
                };
                $scope.aceitar = function () {
                    parceiroService.aceitar($scope).then(function (response) {
                        $scope.dadosAceitacao = response.data[0];
                        $scope.aviso = true;
                        chamadaServico2();
                        function chamadaServico2() {
                            parceiroService.statusViagem($scope).then(function (response) {
                                $scope.dadosStatus = response.data[0];
                            });
                        };
                        $scope.killtimer2 = function () {
                            if (angular.isDefined(timer2)) {
                                $interval.cancel(timer2);
                                timer2 = undefined;
                            }
                        };
                        var timer2 = $interval(function () {
                            if ($scope.dadosStatus.status == 9) {
                                $scope.audioCancelado.play();
                                $scope.killtimer2();
                                $scope.inciar = false;
                                $scope.inciou = false;
                                $scope.aviso = false;
                                $scope.buscarViagem();
                            } else if ($scope.dadosStatus.status == 2) {
                                $scope.killtimer2();
                            } else {
                                chamadaServico2();
                            }
                        }, 4000);
                        $scope.avisoChegada = function () {
                            parceiroService.avisoChegada($scope).then(function (response) {
                                chamadaServico2();
                                $scope.dadosavisoChegada = response.data[0];
                                $scope.inciar = true;
                                $scope.iniciou = false;
                                $scope.aviso = false;
                            });
                        };
                    });
                };
                $scope.iniciar = function () {
                    parceiroService.iniciar($scope).then(function (response) {
                        $scope.dadosInicioViagem = response.data[0];
                        $scope.tarifa = $scope.dadosInicioViagem.tarifa;
                        $scope.inciar = false;
                        $scope.iniciou = true;
                        $scope.aviso = false;
                    });
                };
                $scope.recusar = function () {
                    parceiroService.recusar($scope).then(function (response) {
                        $scope.dadosRecusa = response.data[0];
                        $scope.buscarViagem();
                    });
                };
                $scope.pagamento = function () {
                    parceiroService.getFormaPagamento().then(function (data) {
                        $scope.formasPagamento = data.data;
                    });
                };


                $scope.clickAvaliacao = function (param) {
                    $scope.avaliacao = param;
                };
                $scope.avaliar = function () {
                    var objAvaliacao = {
                        idSolicitacao: $scope.dadosAceitacao.idSolicitacao,
                        nota: $scope.avaliacao
                    }
                    parceiroService.avaliarUsuario(objAvaliacao).then(function (response) {
                        $scope.buscarViagem();
                    });
                    $scope.inciar = false;
                    $scope.iniciou = false;
                    $scope.aviso = false;
                };
                $scope.finalizar = function (formaPagamento) {
                    var objEnvio = {
                        id: $scope.parceiro.id,
                        placa: $scope.parceiro.placa,
                        formaPagamento: formaPagamento
                    };
                    parceiroService.finalizar(objEnvio).then(function () {

                    });
                };

                $scope.buscarViagem = function () {
                    $scope.loading = true;
                    $scope.encontrou = false;
                    $scope.avaliacao = 0;
                    //id usuario volta
                    chamadaServico();
                    function chamadaServico() {
                        parceiroService.buscarViagem($scope).then(function (response) {
                            $scope.dadosViagem = response.data[0];
                        });
                    };
                    $scope.killtimer = function () {
                        if (angular.isDefined(timer)) {
                            $interval.cancel(timer);
                            timer = undefined;
                        }
                    };
                    var timer = $interval(function () {
                        if ($scope.dadosViagem.status == 1) {
                            $scope.audioChegadaViagem.play();
                            $scope.killtimer();
                            $scope.encontrou = true;
                            $scope.loading = false;
                        } else {
                            chamadaServico();
                        }
                    }, 4000);

                    $scope.cancelar = function () {
                        $scope.killtimer();
                        $scope.loading = false;
                        var objEnvio = {
                            id: $scope.parceiro.id,
                            placa: $scope.parceiro.placa
                        };
                        parceiroService.cancelarViagem(objEnvio).then(function (response) { });
                        $scope.buscarViagem();
                    };
                };
            }
        });
    }]);
