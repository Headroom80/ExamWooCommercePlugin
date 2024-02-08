console.log('countdown.js loaded');
document.addEventListener('DOMContentLoaded', function () {
    //ici on va chercher l'élément qui contient l'attribut data-event-date
    var eventElement = document.getElementById('event-details')
    console.log(eventElement);
    //si l'élément existe
    if (eventElement) {
        // alors on récupère la valeur de l'attribut data-event-date
        var eventDate = eventElement.getAttribute('data-event-date');
        console.log(eventDate);
        //countDownDate est la date de l'événement, on cree un objet Date avec la valeur de l'attribut data-event-date
        var countDownDate = new Date(eventDate).getTime();
        console.log(countDownDate);
        // ici on crée un intervalle qui va se répéter toutes les secondes, et qui va mettre à jour le contenu de l'élément avec l'id countdown
        // avec le temps restant avant l'événement
        var x = setInterval(function () {
            var now = new Date().getTime();
            var distance = countDownDate - now;
            //ici des mathématique sombre pour convertir le temps en jours, heures, minutes et secondes.
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            //on met à jour le contenu de l'élément avec l'id countdown.
            document.getElementById("countdown").innerHTML = days + "j " + hours + "h " + minutes + "m " + seconds + "s ";
            // si le compte à rebours est terminé, on affiche un message.
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "L'événement est terminé";
            }
        }, 1000);
    }
});
