export let _fetch = function(method, uri, body)
{
	let request = {
		method: method,
		headers: {
			'Content-Type': 'application/json',
			'Accept': 'application/json'
		}
	};

	if (body)
		request['body'] = body;

	return fetch(BASE_URL +'/api/'+ uri, request)
		.then((response) => {

			if (response.status === 403)
			{
				window.location = BASE_URL + LOGOUT_URI; 
				return;
			}

			if (response.status === 204)
				return response;
		
			const contentType = response.headers.get('content-type');
			return (contentType && contentType.indexOf('application/json') !== -1
				? response.json()
				: response.text()
			);
		})
		.catch((event) => {
			// TODO : Remove console.log for production
			console.log(event);
			throw event;
		});
}