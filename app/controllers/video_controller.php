<?php

require_once SYSTEM.'controller.php';
require_once SYSTEM.'actions.php';
require_once SYSTEM.'view_response.php';
require_once SYSTEM.'view_message.php';
require_once SYSTEM.'redirect_response.php';
require_once SYSTEM.'json_response.php';

require_once MODEL.'video.php';
require_once MODEL.'comment.php';
require_once MODEL.'user_channel.php';
require_once MODEL.'channel_action.php';
require_once MODEL.'upload.php';
require_once MODEL.'playlist.php';
//require_once MODEL.'predefined_description.php';

class VideoController extends Controller {

	public function __construct() {
		$this->denyAction(Action::INDEX);
	}

	public function get($id, $request, $playlist = false) {
		if(!Video::exists($id)) {
			return Utils::getNotFoundResponse();
		}

		$video = Video::find($id);
		$author = UserChannel::find($video->poster_id);

		if($request->acceptsJson()) {
			$videoData = array(
				'id' => $video->id,
				'title' => $video->title,
				'author' => $video->poster_id,
				'description' => $video->description,
				'views' => $video->views,
				'likes' => $video->likes,
				'dislikes' => $video->dislikes
			);

			return new JsonResponse($videoData);
		}
		
		$data = array();
		$data['currentPageTitle'] = $video->title;
		
		if($video->isSuspended()) {
			$data['author'] = $author;
			$data['video'] = $video;
			
			return new ViewResponse('video/suspended', $data);
		}
		elseif($video->isPrivate() && $video->poster_id != Session::get()->getMainChannel()->id) {
			$data['author'] = $author;
			$data['video'] = $video;
			
			return new ViewResponse('video/private', $data);
		}
		
		if ($playlist != false) {
			$videos_ids = json_decode($playlist->videos_ids);
			foreach($videos_ids as $key => $value) {
				if ($value == $video->id) {
					$nextKey = (isset($videos_ids[$key+1])) ? $key+1 : 0;
					break;
				}
			}
			$data['nextVideo'] = WEBROOT.'playlists/'.$playlist->id.'/watch/'.$videos_ids[$nextKey];
		}
		
		$ext = explode('.', $video->url);
		$ext = $ext[count($ext) - 1];
		$data['playlist'] = $playlist;
		$data['video'] = $video;
		$data['ext'] = $ext;
		$data['playlists'] = array();
		$data['channels'] = (Session::isActive()) ? Session::get()->getOwnedChannels() : array();
		foreach ($data['channels'] as $chan) {
			$data['playlists'][$chan->id] = Playlist::all(array('conditions' => array('channel_id = ?', $chan->id), 'order' => 'timestamp desc'));
		}
		$data['title'] = $video->title;
		$data['tags'] = array();
		$tags = explode(' ', $video->tags);
		foreach ($tags as $tag) {
			$tag = trim($tag);
			$tag = str_replace(',', '', $tag);
			$tag = str_replace(';', '', $tag);
			$tag = str_replace(':', '', $tag);
			$tag = str_replace('.', '', $tag);
			$data['tags'][] = $tag;
		}
		$data['poster_id'] = $video->poster_id;
		$data['author'] = $author;
		$data['description'] = $video->description;
		$data['views'] = $video->views;
		$data['likes'] = $video->likes;
		$data['dislikes'] = $video->dislikes;
		$data['thumbnail'] = $video->getThumbnail();
		$data['subscribers'] = $author->getSubscribersNumber();
		$data['subscribed'] = Session::isActive() ? Session::get()->hasSubscribedToChannel($author->id) : false;
		$data['likedByUser'] = Session::isActive() ? $video->isLikedByUser(Session::get()->id) : false;
		$data['dislikedByUser'] = Session::isActive() ? $video->isDislikedByUser(Session::get()->id) : false;
		$data['recommendations'] = $video->getAssociatedVideos(4);
		$data['channels'] = Session::isActive() ? Session::get()->getOwnedChannels() : array();
		$data['flagged'] = $video->isFlagged();
		$data['discover'] = $video->discover;

		$data['currentPage'] = "watch";

		
		
		$video->addView();
		return new ViewResponse('video/video', $data);
	}

	public function create($request) {
		$req = $request->getParameters();
		

		
		if (isset($req['channelId'], $req['uploadId']) && UserChannel::find($req['channelId'])->belongToUser(Session::get()->id) && Upload::exists(array('id' => $req['uploadId'], 'channel_id' => $req['channelId']))) {
			
// 			if(isset($req['save-description']) && isset($req['save-description-name']) && isset($req['video-description'])){
// 				$newdesc=array();
// 				$newdesc['name']=$req['save-description-name'];
// 				$newdesc['description']=$req['video-description'];
// 				$newdesc['users_channels_id']=$req['channelId'];
// 				PredefinedDescription::create($newdesc);
// 			}
			
			$upload = Upload::find($req['uploadId']);
			$videoId = $upload->video_id;
			if (isset($req['_FILES_']['video'])) {
				$ext = explode('.', $req['_FILES_']['video']['name']);
				$ext = $ext[count($ext) - 1];
				$videoInfos = Utils::upload($req['_FILES_']['video'], 'vid', $videoId, $req['channelId']);
				$videoPath = $videoInfos[0];
				$duration = $videoInfos[1];
				$thumbnailPath = WEBROOT.'uploads/'.$req['channelId'].'/'.$videoId.'.'.$ext.'.jpg';
				Video::createTemp($videoId, $req['channelId'], $videoPath, $thumbnailPath, $duration);
				return new Response(200);
			}
			else {
				if (isset($req['video-title'], $req['video-description'], $req['video-tags'], $req['video-visibility'])) {
					Video::register($videoId, $req['channelId'], $req['video-title'], $req['video-description'], $req['video-tags'], $req['_FILES_']['upload-tumbnail'], $req['video-visibility']);
				}
				return new RedirectResponse(WEBROOT.'watch/'.$videoId);
			}
		}
		else {
			return new Response(403);
		}
	}

	public function update($id, $request) {
		$req = $request->getParameters();
		if(Session::isActive() && (UserChannel::find(Video::find($id)->poster_id)->belongToUser(Session::get()->id) || Session::get()->isModerator() || Session::get()->isAdmin())) {
			if($video = Video::find($id)) {
				$data = array();
				$data['currentPageTitle'] = $video->title.' - Modification';
				if(isset($req['video-edit-submit'], $req['video-title'], $req['video-description'], $req['video-tags'])) {
					$data['video'] = $video;

					$title = $req['video-title'];
					$description = $req['video-description'];
					$tags = $req['video-tags'];
					$visibility = $req['video-visibility'];

					if(Utils::validateVideoInfo($title, $description, $tags) && in_array($visibility, array(0, 1, 2))) {
						$video->updateInfo($title, $description, $tags, $req['_FILES_']['tumbnail'], $visibility);
						$data['video'] = $video;

						$response = new ViewResponse('video/edit', $data);
						$response->addMessage(ViewMessage::success('Votre video a bien été modifiée !'));

						return $response;
					}
					else {
						$response = new ViewResponse('video/edit', $data);
						$response->addMessage(ViewMessage::error('Les informations ne sont pas valides.'));

						return $response;
					}
				}
				else if(isset($req['flag']) && !empty($req['flag'])) {
					$flag = $req['flag'];

					if($flag == 'false' && (Session::get()->isModerator() || Session::get()->isAdmin())) {
						$video->unFlag(Session::get()->id);
						return new Response(200);
					}
					else if($flag == 'true') {
						$video->flag(Session::get()->id);
						return new Response(200);
					}
				}
				else if(isset($req['suspend']) && !empty($req['suspend']) && (Session::get()->isModerator() || Session::get()->isAdmin())) {
					$suspend = $req['suspend'];

					if($suspend == 'false') {
						$video->unSuspend(Session::get()->id);
						return new Response(200);
					}
					else if($suspend == 'true') {
						$video->suspend(Session::get()->id);
						return new Response(200);
					}
				}
				else if(isset($req['like'])) {
					$userId = Session::get()->id;

					if(!$video->isLikedByUser($userId)) {
						if($video->isDislikedByUser($userId)) {
							$video->removeDislike($userId);
						}

						$video->like($userId);
						return new Response(200);
					}
				}
				else if(isset($req['dislike'])) {
					$userId = Session::get()->id;

					if(!$video->isDislikedByUser($userId)) {
						if($video->isLikedByUser($userId)) {
							$video->removeLike($userId);
						}

						$video->dislike($userId);
						return new Response(200);
					}
				}
				else if(isset($req['unlike'])) {
					$userId = Session::get()->id;

					if($video->isLikedByUser($userId)) {
						$video->removeLike($userId);
						return new Response(200);
					}
					
				}
				else if(isset($req['undislike'])) {
					$userId = Session::get()->id;

					if($video->isDislikedByUser($userId)) {
						$video->removeDislike($userId);
						return new Response(200);
					}
				}
				else if(isset($req['discover']) && (Session::get()->isModerator() || Session::get()->isAdmin())) {
					$video->discover = Utils::tps();
					$video->save();
					return new Response(200);
				}
			}
		}

		return new Response(500);
	}

	public function destroy($id, $request) {
		if(Session::isActive()) {
			if($video = Video::find($id)) {
				$channels=Session::get()->getOwnedChannels();
				$channels_ids= array();
				
				foreach ($channels as $chan) { //Get only the channels ids
					$channels_ids[]=$chan->id;
				}
				if(Session::get()->isAdmin() || Session::get()->isModerator() || in_array($video->poster_id, $channels_ids)){ //Verify if the user own the channel of the video
					
					$video->erase(Session::get()->id);
					return new Response(200);
				}
				return new Response(403);				
			}
			return new Response(404);
		}
		return new Response(500);
	}

	public function edit($id, $request) {
		if(Session::isActive() && UserChannel::find(Video::find($id)->poster_id)->belongToUser(Session::get()->id)) {
			if($video = Video::find($id)) {
				$data = array();
				$data['currentPageTitle'] = $video->title.' - Modification';
				$data['video'] = $video;
				return new ViewResponse('video/edit', $data);
			}
			else
				return new RedirectResponse(WEBROOT.'account/videos');
		}
		else 
			return new RedirectResponse(WEBROOT.'login');
	}

	public function index($request) {}

}