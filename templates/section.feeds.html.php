<section id="feeds" class="paxs">
	<h1 class="pas bigger" x-data="actions"><?= $appname ?>
		<i class="ti ti-logout right" @click="logout" title="<?= __('Logout') ?>"></i>
		<i class="ti ti-settings right" title="<?= __('Settings') ?>"></i>
		<i class="ti ti-cloud-download right" @click="update" title="<?= __('Update') ?>"></i>
	</h1>

	<ul id="views" class="list pan" x-data="views">
		<li class="strong" @click="select('today')">
			<i class="ti ti-inbox"></i> <?= __('Today') ?>

			<span class="view-count meta"
				x-text="counts.today > 0 ? counts.today : ''"></span>
		</li>
		<li class="strong" @click="select('later')">
			<i class="ti ti-pin"></i> <?= __('Read later') ?>

			<span class="view-count meta"
				x-text="counts.later > 0 ? counts.later : ''"></span>
		</li>
		<li class="strong" @click="select('archives')">
			<i class="ti ti-archive"></i> <?= __('Archives') ?>

			<span class="view-count meta"
				x-text="counts.archives > 0 ? counts.archives : ''"></span>
		</li>
	</ul>

	<div class="mtm" x-data="feeds">
		<hgroup>
			<h2 class="small ucase left"><?= __('Feeds') ?></h2>

			<span class="wbtn small right" style="color:var(--color-blue)"
				@click="showForm"><i class="ti ti-circle-plus" title="<?= __('Add a feed') ?>"></i></span>
		</hgroup>

		<ul id="feeds-list" class="list pan clear">
			<template x-for="(feed, feedIndex) in feeds">
				<li @click="select(feed.id, feedIndex)" :class="{'current': feed.id == iCurrentFeedId}">
					<span class="feed-title"
						x-text="feed.attributes.title" :title="feed.attributes.url"></span>

					<span class="feed-count meta"
						x-text="feed.attributes.count_is_unread > 0 ? feed.attributes.count_is_unread : ''"
						:class="{'hidden': feed.bIsUpdating, 'badge-new': feed.attributes.count_is_new}"></span>

					<span class="feed-updating meta hidden" :class="{'hidden': !feed.bIsUpdating}">
						<i class="ti ti-loader-2" title="<?= __('Updating...') ?>" :class="{'spin': feed.bIsUpdating}"></i>
					</span>

					<span class="feed-actions hidden" x-show="!feed.bIsUpdating">
						<span @click.stop="updateFeed(feed, feedIndex)"><i class="ti ti-reload"></i></span>
						<span title="<?= __('Edit feed') ?>" @click.stop="edit(feed.id)"><i class="ti ti-edit"></i></span>
						<span title="<?= __('Delete feed') ?>" @click.stop="askDelete(feed.id, feed.attributes.title)"><i class="ti ti-trash"></i></span>
					</span>
				</li>
			</template>
		</ul>

		<template x-if="bFormVisible">
			<div class="modal">
				<form class="modal-box w50" method="post" @submit.prevent="send">
					<div class="modal-header h3">
						<span x-text="model.id ? '<?= __('Edit a feed') ?>' : '<?= __('Add a feed') ?>'"></span>
					</div>
					<div class="modal-body wform-aligned">
						<input type="hidden" x-model="model.id">
						<div class="wfield mtn">
							<label for="url"><?= __('Feed URL:') ?></label>
							<div class="winput wgrid">
								<input class="w100" type="text" name="url" required x-model="model.url" :class="{'invalid': urlIsInvalid, 'valid': bIsChecked}" @keyup="bIsChecked = false">
							</div>
						</div>
						<div class="wfield">
							<label><?= __('Title:') ?></label>
							<div class="winput">
								<input class="w50" type="text" name="title" required readonly x-model="model.title" :class="{'invalid': titleIsInvalid}">
							</div>
						</div>
						<div class="wfield" x-show="sFormFeedback">
							<div class="winput wcallout wdanger" x-text="sFormFeedback"></div>
						</div>
					</div>
					<div class="modal-footer tri">
						<button class="left" @click="hideForm"><?= __('Cancel') ?></button>

						<button class="wbtn-blue" type="button"
							@click="check" :disabled="bIsChecking"><?= __('Check') ?></button>

						<button class="wbtn-primary" type="submit"
							:disabled="!bIsChecked || bIsSending" x-text="model.id ? '<?= __('Confirm') ?>' : '<?= __('Add') ?>'"></button>
					</div>
				</form>
			</div>
		</template>
	</div>
</section>