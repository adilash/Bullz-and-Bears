(function() {

	'use strict';

	angular
		.module('moments', ['ui.router', 'satellizer','ui.materialize','AppControllers','AppConfig'])
		.config(function($stateProvider, $urlRouterProvider, $authProvider, $httpProvider, $provide, config) {

			function redirectWhenLoggedOut($q, $injector) {
				return {
					responseError: function(rejection) {

						var $state = $injector.get('$state'); //can't inject state else circular dependency
						var rejectionReasons = ['token_not_provided', 'token_expired', 'token_absent', 'token_invalid'];
						angular.forEach(rejectionReasons, function(value, key) {
							if(rejection.data.error === value) {
								localStorage.removeItem('user');
								$state.go('auth');
							}
						});
						return $q.reject(rejection);
					}
				}
			}

			$provide.factory('redirectWhenLoggedOut', redirectWhenLoggedOut);
			$httpProvider.interceptors.push('redirectWhenLoggedOut');

			$authProvider.loginUrl = config.baseUrl + 'api/authenticate';

			$urlRouterProvider.otherwise('/auth');

			$stateProvider
				.state('auth', {
					url: '/auth',
					templateUrl: './views/authView.html',
					controller: 'AuthController'
				})
				.state('users', {
					url: '/users',
					templateUrl: './views/userView.html',
					controller: 'UserController'
				});
		})

		.run(function($rootScope, $state,$auth) {

			$rootScope.$on('$stateChangeStart', function(event, toState) {

				console.log('stateChange:');
				var user = JSON.parse(localStorage.getItem('user'));
				console.log(user);

				if(user){

					$rootScope.authenticated = true;
					$rootScope.currentUser = user;

					if(toState.name === "auth") {

						event.preventDefault();
						$state.go('users');

					}
				}

			});

		 	$rootScope.logout = function() {

				$auth.logout().then(function() {


					localStorage.removeItem('user');
					$rootScope.authenticated = false;
					$rootScope.currentUser = null;

				});
			}

		});
})();
