import {views} from './components/views.js';
import {feeds} from './components/feeds.js';
import {articles} from './components/articles.js';
import {article} from './components/article.js';

document.addEventListener('alpine:init', () =>
{
	/**
	 * Current view among 'today, later, archives'
	 */
	Alpine.store('view', 'today');

	/**
	 * Main actions.
	 */
	Alpine.data('actions', () => ({

		logout() { window.location = BASE_URL + LOGOUT_URI; },
		update() { this.$dispatch('articles.fetch'); }
	}));

	/**
	 * Feeds views. 
	 */
	Alpine.data('views', () => (views));

	/**
	 * Feeds list.
	 */
	Alpine.data('feeds', () => (feeds));

	/**
	 * Selected feed articles pane.
	 */
	Alpine.data('articles', () => (articles));

	/**
	 * Selected article pane.
	 */
	Alpine.data('article', () => (article));

	/**
	 * Delete article with confirmation.
	 */
	Alpine.data('deleteArticle', (iArticleIndex = -1) => ({
		iIndex: iArticleIndex,
		bConfirm: false,
		
		trigger:
		{
			['x-show']() { return !this.bConfirm; },
			['@click.stop']() { this.bConfirm = true; }
		},
		
		confirm: 
		{
			['x-show']() { return this.bConfirm; },
			['@mouseleave']() { this.bConfirm = false; },
			['@click.stop']()
			{
				this.$dispatch('articles.delete', this.iIndex);
				this.bConfirm = false;
			}
		}
	}));

	/**
	 * Archive article with confirmation.
	 */
	Alpine.data('archiveArticle', (iArticleIndex = -1) => ({
		iIndex: iArticleIndex,
		bConfirm: false,
		
		trigger:
		{
			['x-show']() { return !this.bConfirm; },
			['@click.stop']() { this.bConfirm = true; }
		},
		
		confirm: 
		{
			['x-show']() { return this.bConfirm; },
			['@mouseleave']() { this.bConfirm = false; },
			['@click.stop']()
			{
				this.$dispatch('articles.archive', this.iIndex);
				this.bConfirm = false;
			}
		}
	}));

	/**
	 * Confirm window.
	 */
	Alpine.data('confirm', () => ({
		open: false,
		title: '',
		message: '',
		confirmedEvent: '',
		confirmedData: null,

		init()
		{
			window.addEventListener('confirm', (event) => {
				this.open = true;
				this.title = event.detail.title;
				this.message = event.detail.message;
				this.confirmedEvent = event.detail.confirmedEvent;
				this.confirmedData = event.detail.confirmedData;
			});
		},

		cancel()
		{
			this.open = false;
		},

		confirm()
		{
			this.$dispatch(this.confirmedEvent, this.confirmedData);
			this.open = false;
		}
	}));
});