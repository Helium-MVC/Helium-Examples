Vue.use(axios);
Vue.use(Croppa);
Vue.use(VueMce);

let HTTP = axios.create({
  baseURL: '/api',
  transformRequest: [function (data, headers) {
    headers['Authorization'] = document.getElementById('api_token').value;
    return JSON.stringify(data)
  }],
  headers: {
    'Content-Type': 'application/json'
  }
});


new Vue({
	el : "#app",
	data : {
		//For Image Cropping
		myCroppa : {},
		//Statuses
		updateInfoMessage : '',
		updateEmailMessage : '',
		updatePasswordMessage : '',
		successMessage : '',
		errorMessage : '',
		validationMessage : '',
		//Data
		user_id : '',
		first_name : '',
		last_name : '',
		email : '',
		bio : '',
		github_profile : '',
		password : '',
		csrf_token : '',
		//TinyMCE Config
		tinymceConfig : {
			theme : 'modern',
			menubar : false,
			statusbar : false,
			plugins : 'autolink lists link',
			toolbar1 : 'bold italic strikethrough underline link numlist bullist outdent indent',
		}
	},
	//Executed when the component loads
	created : function() {

		let that = this;

		let user_id = $("#user_id").val();
		
		if (user_id) {
			this.user_id = user_id;
			
			//Assigns the found user to the scope
			HTTP.get('/findUser/' + user_id).then(function(response) {
				that.first_name = response.data.first_name;
				that.last_name = response.data.last_name;
				that.bio = response.data.bio;
				that.email = response.data.email;
				that.github_profile = response.data.github_profile;
				that.job_title = response.data.job_title;
			}).catch(function(e) {
				console.log('Error');
				console.log(e);
			});

		}
	},
	methods : {
		updateImage : function() {

			let that = this;

			HTTP.get('/members/session').then(function(response) {
				let user_id = response['data']['user_id'];

				if (user_id) {
					that.myCroppa.generateBlob(function(blob) {

						var fd = new FormData();
						fd.append('image', blob);

						fd.append('transfer_id', user_id);
						fd.append('transfer', 'user');

						HTTP.post('/uploadImage', fd).then(function(res) {
							location.reload();
						}).catch(function(err) {
							console.log(err.response.data)
						});

					}, 'image/jpeg', 0.8);
				}//end if
			}).catch(function(e) {
				console.log('Error');
				console.log(e);
			});
		},
		//Executes when a user registers
		login : function() {
			
			//Save the scope
			let that = this;

			//Get the data
			let data = {
				email : this.email,
				password : this.password,
				csrf_token : this.csrf_token
			};

			//Attempt to register user
			HTTP.post('/loginUser', data).then(function(res) {
				that.successMessage = '<div class="alert alert-success" role="alert">Information successfully updated</div>';
				
				window.location = '/users/profile/' + res.data.user_id;
				
				setTimeout(function() {
					that.generalSuccessMessage = '';
				}, 6000);

			}).catch(function(err) {
				
				if (err && err.response) {
					
					that.errorMessage = err.response.data;

					setTimeout(function() {
						that.errorMessage = '';
					}, 6000);
				}
			});
		},
		//Executes when a user registers
		register : function() {
			
			//Save the scope
			let that = this;

			//Get the data
			let data = {
				first_name : this.first_name,
				last_name : this.last_name,
				email : this.email,
				password : this.password,
				csrf_token : this.csrf_token
			};

			//Attempt to register user
			HTTP.post('/registerUser', data).then(function(res) {
				that.successMessage = '<div class="alert alert-success" role="alert">Information successfully updated</div>';
				
				window.location = '/users/profile/' + res.data.user_id;
				
				setTimeout(function() {
					that.generalSuccessMessage = '';
				}, 6000);

			}).catch(function(err) {
				
				if (err && err.response) {
					
					that.errorMessage = err.response.data;

					setTimeout(function() {
						that.errorMessage = '';
					}, 6000);
				}
			});
		},
		//Updates a users information
		updateInfo : function() {
			
			//Save the scope
			let that = this;

			//Gather the data
			let data = {
				first_name : this.first_name,
				last_name : this.last_name,
				bio : this.bio,
				github_profile : this.github_profile,
				csrf_token : this.csrf_token
				
			};

			//Attempt to update the user
			HTTP.post('/updateUser/' + this.user_id, data).then(function(res) {
				that.updateInfoMessage = '<div class="alert alert-success" role="alert">Information successfully updated</div>';
				
				setTimeout(function() {
					that.updateInfoMessage = '';
				}, 6000);

			}).catch(function(err) {
				
				if (err && err.response) {
					
					that.errorMessage = err.response.data;

					setTimeout(function() {
						that.errorMessage = '';
					}, 6000);
				}
			});
		},
		//Updates a users Password
		updatePassword : function() {
			
			//Save the scope
			let that = this;

			//Gather the data
			let data = {
				user_id : this.user_id,
				user_password : this.password,
			};

			//Attempt to update the user
			HTTP.post('/updatePassword/' + this.user_id, data).then(function(res) {
				that.updatePasswordMessage = '<div class="alert alert-success" role="alert">Password successfully updated</div>';
				
				setTimeout(function() {
					that.updatePasswordMessage = '';
				}, 6000);

			}).catch(function(err) {
				
				if (err && err.response) {
					
					that.updatePasswordMessage = err.response.data;

					setTimeout(function() {
						that.updatePasswordMessage = '';
					}, 6000);
				}
			});
		},
		updateEmail : function() {
			
			//Save the scope
			let that = this;

			//Gather the data
			let data = {
				user_id : this.user_id,
				email : this.email,
				csrf_token : this.csrf_token
			};

			//Attempt to update the user
			HTTP.post('/updateEmail/' + this.user_id, data).then(function(res) {
				that.updateEmailMessage = '<div class="alert alert-success" role="alert">Email successfully updated</div>';
				
				setTimeout(function() {
					that.updateEmailMessage = '';
				}, 6000);

			}).catch(function(err) {
				
				if (err && err.response) {
					
					that.updateEmailMessage = err.response.data;

					setTimeout(function() {
						that.updateEmailMessage = '';
					}, 6000);
				}
			});
		}
	},
	
}); 