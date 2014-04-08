module.exports = {
	
	options: {
		mangle: true,
		banner : '/*! <%= app.name %> v<%= app.version %> */\n'
	},

	dist: {
		files: {
			
			'../assets/js/min/tipsy.min.js': [ '../assets/js/vendor/tipsy.js'],
			'../assets/js/min/memo.min.js': [ '../assets/js/vendor/memo.js'],
			'../assets/js/min/jquery.parallax.min.js': [ '../assets/js/vendor/jquery.parallax.js'],

			'../assets/js/min/colorpicker.min.js': [ '../assets/js/src/colorpicker.js'],
			'../assets/js/min/upload.min.js': [ '../assets/js/src/upload.js'],
			'../assets/js/min/jquery.panel.min.js': [ '../assets/js/jquery.panel.js'],
			'../assets/js/min/jquery.sections.min.js': [ '../assets/js/jquery.sections.js']
		}
	}
	
};