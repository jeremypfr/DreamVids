/*
 *  DreamPlayer by DimouDev
 *  @_quadrifoglio edit: update 2
 *
 */

player = document.getElementById('player');
videoInfos = document.getElementById('videoInfos');

if (document.body.clientWidth < 640) {
    player.style.width = document.body.clientWidth + 'px';
    player.style.height = document.body.clientWidth / (16 / 9) + 'px';
}
if (videoInfos)
    videoInfos.style.height = player.offsetWidth / (16 / 9) + 'px';

window.addEventListener("resize", function(event) { // Player responsive
    if (document.body.clientWidth < 640) {
        player.style.width = document.body.clientWidth + 'px';
        player.style.height = document.body.clientWidth / (16 / 9) + 'px';
        if (videoInfos)
            videoInfos.style.height = player.offsetWidth / (16 / 9) + 'px';
    } else {
        player.style.width = '';
        player.style.height = '';
        if (videoInfos)
            videoInfos.style.height = player.offsetWidth / (16 / 9) + 'px';
    }
}, false);


video = player.getElementsByTagName('video')[0];

controls = document.getElementById('controls');

playPauseElement = document.getElementById('play-pause');

playPauseElement.addEventListener("click", playPause);

annotationsElement = document.getElementById('annotationsElement');
annotationsElement.addEventListener("click", function(event) {
    if (event.target === annotationsElement) {
        if (controls.className == 'down')
            upPlayer();
        else
            playPause();
    }
});
time = document.getElementById('time');

function playPause() { // Fonction appelÃ©e Ã  chaque play/pause effectuÃ©
    if (video.paused) {
        video.play();
        playPauseElement.style.backgroundImage = "url(dreamplayer/img/player/pause.png)";
    } else {
        video.pause();
        playPauseElement.style.backgroundImage = "url(dreamplayer/img/player/play.png)";
    }
}

function time2str(time) { // Transformation du temps [int] en 13:37
    minutes = Math.floor(time / 60);
    minutes = '00'.substring(0, 2 - ('' + minutes).length) + ('' + minutes);
    seconds = Math.floor(time - minutes * 60);
    seconds = '00'.substring(0, 2 - ('' + seconds).length) + ('' + seconds);
    hours = Math.floor(time / 3600);
    hours = '00'.substring(0, 2 - ('' + hours).length) + ('' + hours);

    return (hours != '00' ? hours + ':' : '') + minutes + ':' + seconds;
}
video.addEventListener("canplay", function() { // Au chargement de la page, on dÃ©finit le bon icone (play ou pause)
    if (video.duration)
        time.innerHTML = time2str(video.currentTime) + ' / ' + time2str(video.duration);

    if (video.paused)
        playPauseElement.style.backgroundImage = "url(dreamplayer/img/player/play.png)";
    else
        playPauseElement.style.backgroundImage = "url(dreamplayer/img/player/pause.png)";
});

bigPlay = document.getElementById('bigPlay');
bigPlay.addEventListener('webkitAnimationEnd', function() {
    this.style.webkitAnimationName = '';
}, false);
bigPlay.addEventListener('msAnimationEnd', function() {
    this.style.msAnimationName = '';
}, false);
bigPlay.addEventListener('oAnimationEnd', function() {
    this.style.oAnimationName = '';
}, false);
bigPlay.addEventListener('animationend', function() {
    this.style.animationName = '';
    this.style.mozAnimationName = '';
}, false);
video.addEventListener("play", function() { // Animation du "play"
    if (video.currentTime != video.duration && video.currentTime != 0) {
        bigPlay.style.webkitAnimationName = 'bigIconPlay';
        bigPlay.style.mozAnimationName = 'bigIconPlay';
        bigPlay.style.msAnimationName = 'bigIconPlay';
        bigPlay.style.oAnimationName = 'bigIconPlay';
        bigPlay.style.animationName = 'bigIconPlay';
    }
});

bigPause = document.getElementById('bigPause');
bigPause.addEventListener('webkitAnimationEnd', function() {
    this.style.webkitAnimationName = '';
}, false);
bigPause.addEventListener('msAnimationEnd', function() {
    this.style.msAnimationName = '';
}, false);
bigPause.addEventListener('oAnimationEnd', function() {
    this.style.oAnimationName = '';
}, false);
bigPause.addEventListener('animationend', function() {
    this.style.animationName = '';
    this.style.mozAnimationName = '';
}, false);
video.addEventListener("pause", function() { // Animation du "pause"
    if (video.currentTime != video.duration) {
        bigPause.style.webkitAnimationName = 'bigIconPause';
        bigPause.style.mozAnimationName = 'bigIconPause';
        bigPause.style.msAnimationName = 'bigIconPause';
        bigPause.style.oAnimationName = 'bigIconPause';
        bigPause.style.animationName = 'bigIconPause';
    }
});

progress = document.getElementById('progress');
progress.buffered = document.getElementById('buffered');
progress.viewed = document.getElementById('viewed');
progress.current = document.getElementById('current');

video.addEventListener("timeupdate", function(event) { // Mise Ã  jour de la progressbar
    percent = video.currentTime / video.duration * 100 + "%"
    progress.viewed.style.width = percent;
    progress.current.style.left = percent;
    if (video.duration)
        time.innerHTML = time2str(video.currentTime) + ' / ' + time2str(video.duration);
});

function bufferUpdate() { // Mise Ã  jour du "buffer"
    if (video.buffered && video.duration && video.buffered.length) {
        percent = video.buffered.end(video.buffered.length - 1) / video.duration * 100;
        progress.buffered.style.width = percent >= 0 && percent <= 100 ? percent + "%" : '0%';
    }
}
video.addEventListener("loadedmetadata", function(event) {
    if (video.buffered)
        setInterval(bufferUpdate, 500);
});

function getOffset(object) {
    if (document.webkitFullscreenElement || document.mozFullscreenElement || document.fullscreenElement) // Bug du fullscreen u_u
        return object.offsetLeft;

    var x = 0;
    element = object;
    while (element) {
        x += (element.offsetLeft - (element == document.body ? 0 : element.scrollLeft) + element.clientLeft);
        element = element.offsetParent;
    }
    return x;
}

mouseDown = false;
progress.addEventListener("mousedown", function(event) { // DÃ©placement de la progressbar
    mouseDown = true;
    if (video.duration)
        video.currentTime = (event.clientX - getOffset(progress)) / progress.offsetWidth * video.duration;
});
document.addEventListener("mouseup", function(event) {
    progress.className = '';
    mouseDown = false;
});

document.addEventListener("mousemove", function(event) {
    if (mouseDown) {
        progress.className = 'clicking';
        percent = (event.clientX - getOffset(progress)) / progress.offsetWidth * 100 + "%"
        progress.viewed.style.width = percent;
        progress.current.style.left = percent;
        video.currentTime = (event.clientX - getOffset(progress)) / progress.offsetWidth * video.duration;
    }
});

var lastTime;
var time2down = 4000;

function downPlayer() { // Les controles disparaissent
    if (!video.paused) {
        lastTime = new Date().getTime();
        annotationsElement.style.cursor = 'none';
        controls.className = 'down';
    }
}
var timeout = setTimeout(downPlayer, time2down / 2);

function upPlayer() { // Les controles rÃ©aparaisent
    if (new Date().getTime() - 300 < lastTime)
        return;

    annotationsElement.style.cursor = 'default';
    controls.className = '';

    clearTimeout(timeout);
    timeout = setTimeout(downPlayer, time2down / 2);
}

player.addEventListener("mousemove", upPlayer);


/*
 *  Fullscreen
 */

widescreen = document.getElementById('widescreen');

widescreen.addEventListener("click", function() {
    console.log(player.className);
    if (player.className == '' || player.className == 'animated') {
        player.className = 'animated wide';
        widescreen.style.backgroundImage = "url(dreamplayer/img/player/smallscreen.png)";
    } else {
        player.className = 'animated';
        widescreen.style.backgroundImage = "url(dreamplayer/img/player/widescreen.png)";
    }
});

/*
 *  Fullscreen
 */

fullscreen = document.getElementById('fullscreen');
fullscreen.addEventListener("click", toogleFullScreen);

function toogleFullScreen() {
    console.log(player.className);
    player.className = (player.className == 'wide' || player.className == 'animated wide') ? 'wide' : '';
    if (!(document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement)) {
        if (player.requestFullscreen)
            player.requestFullscreen();
        else if (player.msRequestFullscreen)
            player.msRequestFullscreen();
        else if (player.mozRequestFullScreen)
            player.mozRequestFullScreen();
        else if (player.webkitRequestFullscreen)
            player.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
    } else {
        if (document.exitFullscreen)
            document.exitFullscreen();
        else if (document.msExitFullscreen)
            document.msExitFullscreen();
        else if (document.mozCancelFullScreen)
            document.mozCancelFullScreen();
        else if (document.webkitExitFullscreen)
            document.webkitExitFullscreen();
    }
}

annotationsElement.addEventListener("dblclick", toogleFullScreen);

document.addEventListener('webkitfullscreenchange', function() {
    if (!document.webkitFullscreenElement)
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/fullscreen.png)";
    else
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/lowscreen.png)";
}, false);
document.addEventListener('mozfullscreenchange', function() {
    if (!document.mozFullScreenElement)
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/fullscreen.png)";
    else
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/lowscreen.png)";
}, false);
document.addEventListener('msfullscreenchange', function() {
    if (!document.msFullscreenElement)
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/fullscreen.png)";
    else
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/lowscreen.png)";
}, false);
document.addEventListener('fullscreenchange', function() {
    if (!document.fullscreenElement)
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/fullscreen.png)";
    else
        fullscreen.style.backgroundImage = "url(dreamplayer/img/player/lowscreen.png)";
}, false);

/*
 *  Volume
 */

volume = document.getElementById('volume');
volume.barre = document.getElementById('barre');
volume.icon = document.getElementById('icon');

video.addEventListener("volumechange", function(event) {
    percent = video.volume * 100 + "%";
    volume.barre.style.width = percent;
    volume.icon.style.left = percent;
    if (video.volume <= 0.05) // GÃ©nÃ©ration de l'image
        volume.icon.style.backgroundImage = "url(dreamplayer/img/player/volume0.png)";
    else if (video.volume <= 0.4)
        volume.icon.style.backgroundImage = "url(dreamplayer/img/player/volume1.png)";
    else if (video.volume <= 0.6)
        volume.icon.style.backgroundImage = "url(dreamplayer/img/player/volume2.png)";
    else if (video.volume >= 0.6)
        volume.icon.style.backgroundImage = "url(dreamplayer/img/player/volume3.png)";
}, false);

mouseDownVolume = false;
volume.addEventListener("mousedown", function(event) { // DÃ©placement de la "barre" de volume
    mouseDownVolume = true;
    newVolume = (event.clientX - getOffset(volume)) / volume.offsetWidth;
    newVolume = newVolume < 0 ? 0 : (newVolume > 1 ? 1 : newVolume);
    video.volume = newVolume;
});
document.addEventListener("mouseup", function(event) {
    mouseDownVolume = false;
});

document.addEventListener("mousemove", function(event) {
    if (mouseDownVolume) {
        percent = video.volume * 100 + "%";
        volume.barre.style.width = percent;
        volume.icon.style.left = percent;
        newVolume = (event.clientX - getOffset(volume)) / volume.offsetWidth;
        newVolume = newVolume < 0 ? 0 : (newVolume > 1 ? 1 : newVolume);
        video.volume = newVolume;
    }
});

function mouseWheelVolume(event) {
    upPlayer();
    if (event.wheelDelta > 0 || event.detail < 0)
        video.volume = Math.min(video.volume + 0.1, 1);
    else
        video.volume = Math.max(video.volume - 0.1, 0);
}

player.addEventListener("mousewheel", mouseWheelVolume, false);
player.addEventListener("DOMMouseScroll", mouseWheelVolume, false);

repeat = document.getElementById('repeat'); // Affichage de repeat en fin de vidÃ©o

video.addEventListener("ended", function() {
    repeat.className = 'show';
}, false);
repeat.addEventListener("click", function() {
    repeat.className = '';
    video.currentTime = 0;
    playPause();
}, false);
video.addEventListener("play", function() {
    repeat.className = '';
}, false);

/*
 *  QualitÃ©
 */

var lastTime = 0;

var currentQuality;
qualitySelection = document.getElementById('qualitySelection'); // On ferme
qualitySelection.addEventListener('click', function() {
    qualitySelection.className = '';
});

qualityButton = document.getElementById('qualityButton'); // On ouvre
qualityButton.addEventListener('click', function() {
    if (qualitys.length == 2) {
        if (qualitys[0].format == currentQuality)
            setQuality(qualitys[1].format)
        else
            setQuality(qualitys[0].format)

    } else if (qualitys.length > 1) // S'il y a plusieurs qualitÃ©s...
        qualitySelection.className = 'show';
});

function setQuality(format) { // On change la qualitÃ©
    qualitySelection.className = '';
    length = qualitys.length;
    if (currentQuality == format)
        return;
    for (var i = 0; i < length; i++) {
        quality = qualitys[i];
        if (quality.format == format) {
            if (format < 720)
                qualityButton.innerHTML = 'SD';
            else if (format < 1080)
                qualityButton.innerHTML = 'HD';
            else
                qualityButton.innerHTML = 'Full HD';

            lastTime = video.currentTime || lastTime;
            if (video.canPlayType('video/mp4')) // Changement de l'url
                video.src = quality.mp4;
            else if (video.canPlayType('video/webm'))
                video.src = quality.webm;
            else
                video.src = quality.mp4;

            currentQuality = format;
            video.load();

            video.addEventListener("loadeddata", function() {
                video.currentTime = lastTime; // On remet au mÃªme moment
                video.play();
            });
        }
    }
}
var qualitys;

function setVideo(array) { // On set toutes les qualitÃ©s au chargement de la page
    if (array.length <= 1)
        qualityButton.style.cursor = 'default';
    else
        qualityButton.style.cursor = 'pointer';

    qualitys = array;
    for (var i = 0, length = qualitys.length; i < length; i++) {
        element = document.createElement("span");
        element.id = 'q' + (i + 1);
        element.innerHTML = qualitys[i].format + 'p';
        element.dataset.format = qualitys[i].format;
        element.addEventListener('click', function() {
            setQuality(this.dataset.format);
        });
        qualitySelection.appendChild(element);
        qualitySelection.dataset.number = i + 1;
    }

    setQuality(array[0].format);
}

/*
 *  Annotations
 */

var annotations;

function closeAnnotation(element) {
    element.style.display = 'none';
}

document.getElementById('annotationsButton').addEventListener('click', function() {
    if (!annotationsElement.className) {
        document.getElementById('annotationsButton').className = 'not';
        annotationsElement.className = 'hide';
    } else {
        document.getElementById('annotationsButton').className = '';
        annotationsElement.className = '';
    }
});

function createAnnotation(id, data) { // CrÃ©ation d'une annotation
    element = document.createElement("span");
    element.id = 'ann' + (id + 1);
    element.className = 'annotation ' + (data.color ? data.color : '');

    element.dataset.start = data.start;
    element.dataset.end = data.end;

    if (data.link)
        element.innerHTML = '<a href="' + data.link + '" target="_blank">' + data.text + "</a>";
    else
        element.innerHTML = "<p>" + data.text + "</p>";
    element.innerHTML += "<span class=\"close\" onclick=\"closeAnnotation(this.parentNode);\"></span>";

    element.style.left = data.left + '%';
    element.style.top = data.top + '%';
    element.style.width = data.width + '%';
    element.style.minHeight = data.height + '%';

    element.addEventListener('click', function() {
        setQuality(this.dataset.format);
    });
    annotationsElement.appendChild(element);
}

video.addEventListener("timeupdate", function(event) { // Mise Ã  jour des annotations
    if (annotations) {
        currentTime = video.currentTime;
        children = annotationsElement.children;

        for (var length = children.length, i = length - 1; i > -1; i--) {
            if (currentTime < children[i].dataset.start || currentTime > children[i].dataset.end) {
                if (children[i].style.display != 'none')
                    annotationsElement.removeChild(children[i]);
            }
        }

        for (var i = 0, length = annotations.length; i < length; i++) {
            if (!document.getElementById('ann' + (i + 1))) {
                if (currentTime > annotations[i].start && currentTime < annotations[i].end)
                    createAnnotation(i, annotations[i]);
            }
        }
    }
});

function setAnnotations(array) { // On set toutes les annotations au chargement de la page
    if (array.length) {
        document.getElementById('annotationsButton').style.display = 'block';
        annotations = array;
        for (var i = 0, length = annotations.length; i < length; i++) {
            if (annotations[i].start == 0)
                createAnnotation(i, annotations[i]);
        }
    }
}

/*
 *  Raccourcis claviers
 */

document.addEventListener("keydown", function(event) {
    if (event.target != document.body)
        return;

    if (event.keyCode == 32) { // [espace]
        playPause();
        upPlayer();
    }

    if (event.keyCode == 70) // [F]
        toogleFullScreen();

    if (event.keyCode == 36) { // [dÃ©but]
        video.currentTime = 0;
        video.play();
        progress.viewed.style.width = '0%';
        progress.current.style.left = '0%';
    }

    if (event.keyCode == 35) { // [fin]
        video.currentTime = video.duration;
        progress.viewed.style.width = '100%';
        progress.current.style.left = '100%';
    }

    if (event.keyCode == 38) // [haut]
        video.volume = Math.min(video.volume + 0.1, 1);

    if (event.keyCode == 40) // [bas]
        video.volume = Math.max(video.volume - 0.1, 0);

    if (event.keyCode == 37) // [gauche]
        video.currentTime -= 3;

    if (event.keyCode == 39) // [droite]
        video.currentTime += 3;
});