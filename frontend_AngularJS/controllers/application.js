

    var marcApp=angular.module('marcipulamiApp', ['ngRoute','ngCookies']);

    marcApp.factory('dataFactory',function() {
      var user={};
      var userId='';
      var userPseudo='';
      var logged=false;
      user.setDonne=function(id,username) {
        userId=id;
        userPseudo=username;
        logged=true;
      }
      user.getId=function(){
        return userId;
      };
      user.getPseudo=function(){
        return userPseudo;
      }
      user.isLogged=function() {
        return logged;
      }
      return user;
    });

    marcApp.config(function($routeProvider) {
      $routeProvider
        .when('/',
        {
          //controller  : 'loginController',
          templateUrl : 'templates/Accueil.html'
        })
        .when('/login', {
            templateUrl : 'templates/login.html'
        })
        .when('/accueil', {
            templateUrl : 'templates/Accueil.html'
        })
        .when('/register', {
            templateUrl : 'templates/register.html'
        })
        .when('/profile',{
            templateUrl : 'templates/profile.html'
        });
    });

    marcApp.controller('loginController', function($scope,$http,dataFactory,$location,$cookies) {
      $("#message").hide();
      $scope.login=function() {
        if (!angular.isUndefined($scope.mdp) && !angular.isUndefined($scope.pseudo)) {
          $scope.res=$scope.mdp+" "+$scope.pseudo;
          datas={"pseudo" :$scope.pseudo,"mdp":$scope.mdp};
          $http({
            method : "POST",
            url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/Accueil",
            headers: {
              'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
              'Content-Type': 'application/x-www-form-urlencoded'
          	},
          	data:datas,
          }).then(function mySuccess(response) {
            if (response.data['status']=='success') {
              dataFactory.setDonne(response.data['id'],response.data['username']);
              $cookies.iduser = dataFactory.getId().toString();
              $cookies.pseudo = dataFactory.getPseudo();
              $scope.res1 = $cookies['iduser'];

              $location.path('/accueil');
            }
            else {
              $scope.res1 = response.data['error'];
            }
          });
        }
        else{
          $scope.res="inserer qq chose";
        }
      };

      $scope.insererNV=function(idTag) {
        angular.element(idTag).removeClass("is-invalid");
      };
      $scope.register=function() {
          datas={"user":$scope.nvlUser};
          if (angular.isUndefined($scope.nvlUser.pseudo)) {
            angular.element('#pseudo').addClass("is-invalid");
          }
          if (angular.isUndefined($scope.nvlUser.email)) {
            angular.element('#email').addClass("is-invalid");

          }
          if (!angular.isNumber($scope.nvlUser.age)) {
            angular.element('#age').addClass("is-invalid");
          }
          if (angular.isUndefined($scope.nvlUser.race)) {
            angular.element('#race').addClass("is-invalid");
          }
          if (angular.isUndefined($scope.nvlUser.famille)) {
            angular.element('#famille').addClass("is-invalid");
          }
          if (angular.isUndefined($scope.nvlUser.nourriture)) {
            angular.element('#nourriture').addClass("is-invalid");
          }
          if (angular.isUndefined($scope.nvlUser.mdp)) {
            angular.element('#password').addClass("is-invalid");
          } 
          if (angular.isUndefined($scope.nvlUser.mdpconf)) {
            angular.element('#confpassword').addClass("is-invalid");
          }
          if (!angular.equals($scope.nvlUser.mdp,$scope.nvlUser.mdpconf)) {
            angular.element('#confpassword').addClass("is-invalid");
            $scope.res1 = $scope.nvlUser.mdpconf;
          } 
          
          $http({
            method : "POST",
            url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/createUser",
            headers: {
              'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            data:datas,
          }).then(function mySuccess(response) {
            if (response.data['status']=='success') {
              $("#message").show(1000).delay(5000).hide(1000);
              //$location.path('/login');
            }
            else {
              $scope.res1 = response.data['error'];
            }
          });
        
      };
    }).controller('listeController', function($scope,$http,dataFactory,$location,$cookies) {

        tosend={"iduser" :$cookies['iduser'],"iper" :$cookies['iduser']};
        $http({
          method : "POST",
          url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/users",
          headers: {
            'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
            'Content-Type': 'application/x-www-form-urlencoded'
        	},
        	data:tosend,
        }).then(function mySuccess(response) {
          if (response.data['status']=='success') {
            $scope.users =response.data['users']; ;
            $scope.amis =response.data['amis']; ;
            $scope.username =response.data['username']; ;
          }
          else {
            $scope.res2 = response.data['error'];
          }
        });
    })
    .controller('ajouterAmiController',function($scope,$http,dataFactory,$location,$cookies,$route) {
        $scope.ajouterAmi=function(){
            tosend={"iduser" :$cookies['iduser'],"iper" :$cookies['iduser']};
            $http({
              method : "GET",
              url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/ajouterAmi/"+$scope.autre.id,
              headers: {
                'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              params:tosend,
            }).then(function mySuccess(response) {
              if (response.data['status']=='success') {
                $scope.users =response.data['users'];
                $scope.amis =response.data['amis'];
                $scope.username =response.data['username'];
                $route.reload();
              }
              else {
                $scope.res2 = response.data['error'];
              }
            });
        };
    })
    .controller('supprimerAmiController',function($scope,$http,dataFactory,$location,$cookies,$route) {
        $scope.supprimerAmi=function(){
            tosend={"iduser" :$cookies['iduser'],"iper" :$cookies['iduser']};
            $http({
              method : "GET",
              url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/supprimerAmi/"+$scope.ami.id,
              headers: {
                'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
                'Content-Type': 'application/x-www-form-urlencoded'
              },
              params:tosend,
            }).then(function mySuccess(response) {
              if (response.data['status']=='success') {
                $scope.users =response.data['users'];
                $scope.users =response.data['amis'];
                $scope.username =response.data['username'];
                $route.reload();

              }
              else {
                $scope.res2 = response.data['error'];
              }
            });
        };
    })
    .controller('profileController',function($scope,$http,dataFactory,$location,$cookies,$route) {
        tosend={"iduser" :$cookies['iduser']};
        $http({
          method : "GET",
          url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/myprofile",
          headers: {
            'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          params:tosend,
        }).then(function mySuccess(response) {
          if (response.data['status']=='success') {
            $scope.username =$cookies['pseudo']; ;

            $scope.userProfile = response.data['profile'];

          }
          else {
            $scope.res2 = response.data['error'];
          }
        });
        
    })
    .controller('modifierProfileController',function($scope,$http,dataFactory,$location,$cookies,$route) {
        
        $scope.validerModif=function() {
          tosend={"iduser" :$cookies['iduser'],"NvProfil":$scope.userProfile};
          $http({
            method : "POST",
            url : "http://localhost/projetSymfony/projetTest/web/app_dev.php/modifprofile",
            headers: {
              'Accept': 'application/json, application/xml, text/plain, text/html, *.*',
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            data:tosend,
          }).then(function mySuccess(response) {
            if (response.data['status']=='success') {
              $scope.userProfile = response.data['profile'];
              $route.reload();
            }
            else {
              $scope.res2 = response.data['error'];
            }
          });
        };

    })
    .controller('logOutController',function($scope,$http,dataFactory,$location,$cookies,$route) {
      $scope.logout=function() {
        delete $cookies['pseudo'];
        delete $cookies['iduser'];
        $location.path('/login');
      };  
    });


marcApp.run(['$rootScope', '$location', 'dataFactory','$cookies', function ($rootScope, $location, dataFactory,$cookies) {
        $rootScope.$on('$routeChangeStart', function (event) {
            if (!$cookies['pseudo'] &
            !$cookies['iduser'] & [ '/','/accueil','/register','/profile'].includes($location.path())  ) {
                console.log('DENY');
                event.preventDefault();
                if ($location.path().indexOf('register')===-1) {
                  $location.path('/login');
                }
                else{
                  $location.path('/register');
                }
            }
            else {
                console.log('ALLOW');
            //    console.log($cookies['iduser']);
            //    console.log($cookies['pseudo']);
            }
        });
    }]);
