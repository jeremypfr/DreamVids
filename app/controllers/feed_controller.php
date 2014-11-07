<?php

require_once SYSTEM.'controller.php';
require_once SYSTEM.'actions.php';
require_once SYSTEM.'view_response.php';
require_once SYSTEM.'redirect_response.php';

require_once MODEL.'video.php';
require_once MODEL.'user_channel.php';
require_once MODEL.'comment.php';

class FeedController extends Controller {

	public function __construct() {
		$this->denyAction(Action::GET);
		$this->denyAction(Action::CREATE);
		$this->denyAction(Action::UPDATE);
		$this->denyAction(Action::DESTROY);
	}

	public function index($request) {
		if(Session::isActive()) {
			$sess = Session::get();
			$data = array();
			$data['currentPageTitle'] = 'Flux d\'activité';
			$data['actions'] = array();
			$data['subscriptions'] = array();
			$data['last_visit'] = $sess->last_visit;
			$sess->last_visit = Utils::tps();
			$sess->save();
			
			$actions = Session::get()->getNotifications();
			
			$data['subscriptions'] = Session::get()->getSubscriptions();
			if(count($actions) > 0) {
				$data['actions'] = $actions;
			}

			return new ViewResponse('feed/feed', $data);
		}
		else {
			return new RedirectResponse(WEBROOT.'login');
		}
	}

	public function subscription($subscriptionId) {
		$subscription = UserChannel::exists($subscriptionId) ? UserChannel::find($subscriptionId) : UserChannel::find_by_name($subscriptionId);

		if(is_object($subscription) && !$subscription->belongToUser(Session::get()->id)) {
			$data = array();

			$data['subscriptions'] = Session::get()->getSubscriptions();
			$data['vids'] = Session::get()->getSubscriptionsVideosFromChannel($subscription->id, 6);

			return new ViewResponse('feed/feed', $data);
		}
	}

	// Denied actions
	public function get($id, $request) {}
	public function create($request) {}
	public function update($id, $request) {}
	public function destroy($id, $request) {}

}