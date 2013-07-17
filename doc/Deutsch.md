#  XROWQuestionaire

## Einleitung

Mit XROW's "Questionaire" können Sie Umfragen, Quizzes und Gewinnspiele in eZ Publish abbilden.

Das Erweiterung abdeckt folgende Usecases ab. 

* Einfaches Voting
* Mehrseitiges Voting
* Bildervoting
* Quiz
* Gewinnspiel

## Beschreibung der Funktionen

Die Erweiterung beruht durch einen einheitlichen standardisierten Ansatz. Umfragen etc. könnten auf jedweden Content Seiten einfach platziert werden.

Der verfolgte Ansatz beruht darauf, dass das Umfragen etc. als Datentyp zu jeder Klasse hinzugefügt werden können. D.h. Zusatzinformation, die nicht direkt zur Umfrage etc. gehören wie Name Einleitung etc., können einfach erweitert werden. Des Weiteren kann auch, da eine Umfrage als Content Objekt vorliegt, einfach als „embed Objekt“ in Artikeln oder anderen Serviceseiten wie auch Übersichtsseiten und in eZ Flow benutzt werden.

Die Programmierung im Frontend ist AJAX basiert. Bei jedem Auslösen einer Aktion wie Abstimmen oder Ergebnisse anzeigen wird der Aktuelle Block im HTML ausgetauscht. Würden wir eine nicht AJAX basierten Ansatz wählen, hätten wir den Nachteil, dass wir bei der Umfrage den Kontext der Seite verlassen müssten und sich der User eventuell in einem ganz anderen Bereich der Seite wiederfindet und wir auch nicht mehr die Informationen aus dem Content Modul in eZ Publish verwenden können, um Zusatzinformationen rendern. 

Folgende Funktionen stehen dem Redakteur im Allgemeinen zur Verfügung:

* Auswahl Voting nur für eingeloggte User
* Auswahl Validierung vorhandener oder benötigter Userattribute. Bestimmt welche Attribute bei einem User gefüllt sein müssen. Falls dies nicht der Fall ist wird der User zu seinem User Profil geschickt, um die Daten auszufüllen.
* Auswahl Doppeltes abstimmen nicht möglich ( impliziert Teilnahme nur für registrierte Benutzer )
* Auswahl Ergebnis anzeigen
* Auswahl Ist Gewinnspiel, Extra Button „Gewinner ermitteln“ (Random Funktion)
* Auswahl Voting schließen
* Auswahl Ergebnisse zurücksetzen
* Auswahl CAPTCHA Sicherheit zum Blocken von Bots

Folgende Funktionen stehen dem Redakteur beim Erstellen der Fragen und Antworten allgemein zur Verfügung:

* Neue Frage hinzufügen, Bild und/oder Text, Bild per Ajax Browse Funktion
* Bild zu Frage hinzufügen, Bild und/oder Text, Bild per Ajax Browse Funktion
* Neue Antwort hinzufügen, Typen Option oder Notenbewertung
* Bei Typ Option kann eine richtige Option für ein Quiz markiert werden und ein Text hinterlegt werden, falls ein User die Option gewählt hat. Desweiten kann eine Punktzahl hinterlassen werden, falls ein User dieses richtig beantwortet. 
* Layout der Frage einstellen, z.B. Normal Ansicht, Galerieansicht (Bildervoting), Notenvoting

Folgende Funktionen sollten dem Redakteur bei der Präsentation der Ergebnisse zur Verfügung.

* Keine Ergebnis anzeigen
* Ergebnisse anzeigen mit nach erreichter Punktzahl, mehrfaches Erstellen von bis Punktzahl mit Text
* Ergebnisansichtsseite mit den Ergebnissen der anderen User.

Erweiterbarkeit durch Workflow
* Durch einen Tigger vor Anzeige der Ergebnisseite kann ein Workflowevent aufgerufen werden. Dieser Event hat folgende Paramenter UserID oder SessionID, ContentObjectID des Elements. Anhand dieser Werte können Daten an ein Dritt-System übergeben werden.
