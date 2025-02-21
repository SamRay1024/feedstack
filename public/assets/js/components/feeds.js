import * as utils from '../utils.js';

const EMPTY_FEED = { 'id': 0, 'url': '', 'title': '' };

export let feeds = {
	feeds: [],
	iCurrentFeedId: 0,
	model: { ...EMPTY_FEED },
	bFormVisible: false,
	sFormFeedback: '',
	bIsSending: false,
	bIsChecking: false,
	bIsChecked: false,

	init()
	{
		// Create {ModelField}IsInvalid flags
		Object.entries(this.model).forEach((field) => {
			this[field[0] + 'IsInvalid'] = false;
		});

		this.loadFeeds();
		setTimeout(() => { this.fetchArticles(); }, 5000);

		window.addEventListener('feeds.reload', () => { this.loadFeeds(); });
		window.addEventListener('feeds.delete', (event) => { this.delete(event.detail); });
		window.addEventListener('feeds.updateCount', (event) =>
		{
			this.updateCount(event.detail.index, event.detail.name, event.detail.diff);
		});
		window.addEventListener('articles.fetch', () => { this.fetchArticles(); });
	},

	loadFeeds()
	{
		utils._fetch('GET', 'feeds').then(response =>
		{
			if (response.data)
			{
				this.feeds = response.data;
			}
			// TODO : else display error
		});
	},

	fetchArticles()
	{
		Promise.all(this.feeds.map(feed => this.updateFeed(feed)));
	},

	updateFeed(feed, iSelectIndex = -1)
	{
		feed.bIsUpdating = true;

		return utils._fetch('GET', 'fetch/'+ feed.id)
			.then(response =>
			{
				feed.bIsUpdating = false;

				if (iSelectIndex >= 0)
					this.select(feed.id, iSelectIndex);
			});
	},

	showForm()
	{
		this.bFormVisible = true;
	},

	hideForm()
	{
		this.model = { ...EMPTY_FEED };
		this.bFormVisible = false;
		this.sFormFeedback = '';
		this.bIsChecked = false;
	},

	check()
	{
		this.sFormFeedback = '';
		this.bIsChecking = true;

		if (this.model.url == '')
			return;

		utils._fetch('GET', 'check?url='+this.model.url)
		.then(response => 
		{
			if (response.error)
			{
				this.sFormFeedback = 'Cette adresse ne correspond pas à un flux compatible.';
				this.model.title = '';
				this.urlIsInvalid = true;
			}
			else
			{
				this.model.title = response.data.title;
				this.bIsChecked = true;
				this.urlIsInvalid = false;
			}
		})
		.catch(() => { this.sFormFeedback = "Something went wrong."; })
		.finally(() => { this.bIsChecking = false; });
	},

	send()
	{
		if (!this.bIsChecked)
		{
			this.sFormFeedback = 'Vous devez vérifier le flux d\'abord.';
			return;
		}

		this.sFormFeedback = '';
		this.bIsSending = true;

		utils._fetch(
			(this.model.id ? "PUT" : "POST"),
			'feeds' + (this.model.id ? '/' + this.model.id : ''),
			JSON.stringify(this.model)
		)
		.then(response =>
		{
			if (response.error)
			{
				this.sFormFeedback = (response.error.code == 409
					? 'Vous êtes déjà abonné à ce flux !'
					: response.error.detail
				);

				response.error.fields.forEach(name => {
					this[name + 'IsInvalid'] = true;
				});
			}
			else
			{
				fetch(BASE_URL + '/api/fetch/'+ response.data.id,
				{
					method: "GET",
					headers: {
						'Content-Type': 'application/json',
						Accept: 'application/json'
					}
				});

				this.hideForm();
				this.loadFeeds();
			}
		})
		.catch(() => { this.sFormFeedback = "Something went wrong."; })
		.finally(() => { this.bIsSending = false; });
	},

	edit(id)
	{
		utils._fetch('GET', 'feeds/' + id).then(response =>
		{
			this.model.id = id;
			this.model.url = response.data.attributes.url;
			this.model.title = response.data.attributes.title;
			this.showForm();
		});
	},

	askDelete(id, title)
	{
		this.$dispatch('confirm', {
			'title': 'Supprimer un flux',
			'message': 'Confirmez-vous la suppression du flux "'+title+'" ?<br><strong>Cette opération est irréversible.</strong>',
			'confirmedEvent': 'feeds.delete',
			'confirmedData': {'id': id}
		});
	},

	delete(data)
	{
		utils._fetch('DELETE', 'feeds/'+ data.id).then(response =>
		{
			if (response.status >= 200)
				this.loadFeeds();
		});
	},

	select(id, index)
	{
		this.iCurrentFeedId = id;
		this.$dispatch('articles.load.feed', {'id': id, 'index': index});
	},

	updateCount(iFeedIndex, sCountName, iDiff = 0)
	{
		let feed = this.feeds[iFeedIndex];

		if (feed && feed.attributes[sCountName] != undefined)
		{
			feed.attributes[sCountName] += iDiff
		}
	}
}