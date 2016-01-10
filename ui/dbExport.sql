-- ####################### DB NEU ERZEUGEN ###########################
--Bestehende Tabellen löschen
DROP TABLE IF EXISTS RESERVIERUNGEN;
DROP TABLE IF EXISTS SPERRUNGEN;
DROP TABLE IF EXISTS LEHRER;
DROP TABLE IF EXISTS ZEITEN;
DROP TABLE IF EXISTS SCHUELER;
DROP TABLE IF EXISTS EINSTELLUNGEN;

--Tabellen neu erzeugen
CREATE TABLE LEHRER
(
	lehrer_id INTEGER NOT NULL PRIMARY KEY ASC, 
	name VARCHAR(50) NOT NULL,
	raum VARCHAR(30) NOT NULL
);

CREATE TABLE ZEITEN
(
	zeit_id INTEGER NOT NULL PRIMARY KEY ASC, 
	zeit VARCHAR(10) NOT NULL
);

CREATE TABLE SCHUELER
(
	schueler_id INTEGER NOT NULL PRIMARY KEY ASC, 
	name VARCHAR(100) NOT NULL
);

CREATE TABLE EINSTELLUNGEN
(
	name VARCHAR(50) NOT NULL PRIMARY KEY,
	wert VARCHAR(1000) NOT NULL
);

CREATE TABLE RESERVIERUNGEN
(
	lehrer_id INTEGER NOT NULL,
	zeit_id INTEGER NOT NULL,
	schueler_id INTEGER NOT NULL,
	PRIMARY KEY(lehrer_id, zeit_id),
	FOREIGN KEY (lehrer_id) REFERENCES LEHRER(lehrer_id),
	FOREIGN KEY (zeit_id) REFERENCES ZEITEN(zeit_id),
	FOREIGN KEY (schueler_id) REFERENCES SCHUELER(schueler_id)
);

CREATE TABLE SPERRUNGEN
(
	lehrer_id INTEGER NOT NULL,
	zeit_id INTEGER NOT NULL,
	PRIMARY KEY (lehrer_id, zeit_id),
	FOREIGN KEY (lehrer_id) REFERENCES LEHRER(lehrer_id),
	FOREIGN KEY (zeit_id) REFERENCES ZEITEN(zeit_id)
);

-- ####################### DATEN EINFUEGEN ###########################

-- Lehrer einfuegen
<check if="{{ @allLehrer }}">
<repeat group="{{ @allLehrer }}" value="{{ @lehrer }}">
INSERT INTO LEHRER VALUES ( {{ @lehrer.lehrer_id }}, {{ @sqlEscape(@lehrer.name) }}, {{ @sqlEscape(@lehrer.raum) }} );
</repeat>
</check>

-- Zeiten einfuegen
<check if="{{ @allZeiten }}">
<repeat group="{{ @allZeiten }}" value="{{ @zeit }}">
INSERT INTO ZEITEN VALUES ( {{ @zeit.zeit_id }}, {{ @sqlEscape(@zeit.zeit) }} );
</repeat>
</check>

-- Schüler einfuegen
<check if="{{ @allSchueler }}">
<repeat group="{{ @allSchueler }}" value="{{ @schueler }}">
INSERT INTO SCHUELER VALUES ( {{ @schueler.schueler_id }}, {{ @sqlEscape(@schueler.name) }} );
</repeat>
</check>

-- Sperrungen einfuegen
<check if="{{ @allSperrungen }}">
<repeat group="{{ @allSperrungen }}" value="{{ @sperrung }}">
INSERT INTO SPERRUNGEN VALUES ( {{ @sperrung.lehrer_id }}, {{ @sperrung.zeit_id }} );
</repeat>
</check>

-- Reservierungen einfuegen
<check if="{{ @allReservierungen }}">
<repeat group="{{ @allReservierungen }}" value="{{ @reservierung }}">
INSERT INTO RESERVIERUNGEN VALUES ( {{ @reservierung.lehrer_id }}, {{ @reservierung.zeit_id }}, {{ @reservierung.schueler_id }} );
</repeat>
</check>

-- Einstellungen setzen
<check if="{{ @allEinstellungen }}">
INSERT INTO EINSTELLUNGEN (name, wert) 
<repeat group="{{ @allEinstellungen }}" value="{{ @einstellung }}" counter="{{ @ctr }}">
INSERT INTO EINSTELLUNGEN VALUES ( {{ @sqlEscape(@einstellung.name) }}, {{ @sqlEscape(@einstellung.wert) }} );
</repeat>
</check>