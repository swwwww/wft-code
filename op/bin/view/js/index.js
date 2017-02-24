var master = angular.module('serverMaster', ['ui.bootstrap']);

master.factory('codeVersion', ['$http', '$q', function ($http) {
    var getVersions = function () {
    };
}]);

master.directive('httpButton', ['$http', function ($http) {
    return {
        template: '<button ng-disabed="httpLoading"> click </button>',
        transclude: true,
        scope: {
            httpAction: '@',
            httpActionMethod: '@',
            httpActionData: '@'
        },
        restrict: 'A',
        link: function (scope, element, attr) {
            scope.httpLoading = false;
            scope.httpError = false;
            scope.httpActionMethod = scope.httpActionMethod ? scope.httpActionMethod : 'get';
            scope.httpActionData = scope.httpActionData ? scope.httpActionData : {};
            if (typeof scope.httpActionData === 'string') {
                scope.httpActionData = JSON.parse(scope.httpActionData);
            }
            element[0].addEventListener('click', function (e) {
                scope.httpError = false;
                scope.httpLoading = true;
                scope.httpPromise = $http({
                    url: scope.httpAction,
                    method: scope.httpActionMethod,
                    data: scope.httpActionData
                });
                scope.httpPromise.success(function (result) {
                    scope.httpLoading = false;
                }).error(function() {
                    scope.httpLoading = false;
                    scope.httpError = true;
                });
            });
        }
    };
}]);

master.controller('RootCtrl', function ($scope) {
});


master.controller('CodeController', ['$http', '$scope', function ($http, $scope) {
    var updateCodeVersionList = function () {
        $http.get('/code/').success(function (result) {
            var versionList = [];
            for (var key in result.versions) {
                result.versions[key].isOpen = (result.versions[key].version === result.currentVersion);
                var logArray = result.versions[key].log.split('\n')[1].split('|')[2].split(' ');
                result.versions[key].createTime = logArray[1] + ' ' + logArray[2];

                result.versions[key].author = result.versions[key].log.split('\n')[3].split(':')[1];
                versionList.push(result.versions[key]);
            }
            versionList.sort(function (a, b) { return b.version - a.version });
            versionList[0].isOpen = true;
            $scope.versionList = versionList;
            $scope.currentVersion = result.currentVersion;
        });
    };
    $scope.expandAll = function () {
        angular.forEach($scope.versionList, function (version) {
            version.isOpen = true;
        });
    };
    $scope.buildCode = function () {
        $scope.buildCodeLoading = true;
        if ($scope.authorName.length < 1) {
            alert("authorName please");
            return false;
        }
        if ($scope.buildMessage.length < 5) {
            alert("message please");
            return false;
        }
        localStorage.previewLog = $scope.buildMessage;
        localStorage.authorName = $scope.authorName;
        var buildMessage = 'AUTHOR: ' + $scope.authorName + '\n' + $scope.buildMessage;
        $http.post('/code/build', {"token":"crowd_test_agent","buildMessage":buildMessage}).success(function (result) {
            updateCodeVersionList();
            $scope.buildCodeLoading = false;
        }).error(function () {
            $scope.buildCodeLoading = false;
        });
    };
    $scope.selectBuild = function (id) {
        $http.post('/code/select/' + id, {"token":"crowd_test_agent"}).success(function (result) {
            updateCodeVersionList();
        });
    };

    $scope.deleteBuild = function (id) {
        $http.post('/code/delete/' + id, {"token":"crowd_test_agent"}).success(function (result) {
            updateCodeVersionList();
        });
    };
    updateCodeVersionList();
    $scope.buildMessage = localStorage.previewLog;
    $scope.authorName = localStorage.authorName;
}]);


master.controller('ServerListController', ['$interval', '$http', '$scope', function ($interval, $http, $scope) {
    $interval(function () {
        $http.get('/agent/').success(function (result) {
            var onlineServerList = [];
            var previewServerList = [];
            angular.forEach(result.agents, function (agent) {
                agent.load = $.trim(agent.load.replace(/.*load average: /, ''));
                agent.memory = $.trim(agent.memory).split(' ')[0] + 'MB';
                agent.agent.version = $.trim(agent.agent.version);
                agent.ipNumber = parseInt(agent.ip.replace(/\./g, ''), 10);
                if (agent.agent.type === 'preview') {
                    previewServerList.push(agent);
                } else {
                    onlineServerList.push(agent);
                }
            });
            onlineServerList.sort(function (a, b) {
                return a.ipNumber - b.ipNumber;
            });
            $scope.onlineServerList = onlineServerList;
            $scope.previewServerList = previewServerList;
            $scope.previewServerList = previewServerList;
            $scope.currentVersion = result.currentVersion;
        });
    }, 1000);

    $scope.updateWeb = function(ip) {
        return $http.post('/agent/' + ip + '/deploy', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };
    $scope.updateAllWeb = function () {
        return $http.post('/agent/webUpdate', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };
    $scope.updateAllWebDeploy = function () {
        return $http.post('/agent/deploy', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };
    $scope.updatePreviewWebDeploy = function () {
        angular.forEach($scope.previewServerList, function (agent) {
            $http.post('/agent/' + agent.ip + '/deploy', {"token":"crowd_test_agent"}).success(function (result) {
            });
        });
    };
}]);

master.controller('OperationController', ['$http', '$scope', function ($http, $scope) {
    $scope.updateMaster = function () {
        return $http.post('/master/update', {"token":"crowd_test_agent"}).success(function (result) {
            window.location.reload();
        });
    };

    $scope.updateAllAgent = function () {
        return $http.post('/agent/update', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };


    $scope.reRunLogCron = function () {
        return $http.post('/agent/reRunLogCron', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };

    $scope.restartAgentApache = function () {
        return $http.post('/agent/restartApache', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };

    $scope.reRunLogCron = function () {
        return $http.post('/agent/reRunLogCron', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };

    $scope.timeCalibrates = function () {
        return $http.post('/agent/timeCalibrates', {"token":"crowd_test_agent"}).success(function (result) {
        });
    };

    

    $http.get('/master').success(function (result) {
        $scope.remoteVersion = result.remoteVersion;
        $scope.localVersion = result.localVersion;
    });
}]);
