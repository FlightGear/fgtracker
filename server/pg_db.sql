-- Adminer 4.3.1 PostgreSQL dump

\connect "fgtracker";

DROP TABLE IF EXISTS "airports";
CREATE SEQUENCE airports_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."airports" (
    "id" integer DEFAULT nextval('airports_id_seq') NOT NULL,
    "icao" character(4),
    "name" text,
    CONSTRAINT "airports_icao_idx" UNIQUE ("icao"),
    CONSTRAINT "airports_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "cache_time";
CREATE TABLE "public"."cache_time" (
    "tablename" character varying(100) NOT NULL,
    "cachetime" timestamptz DEFAULT now()
) WITH (oids = false);


DROP TABLE IF EXISTS "cache_top100_alltime";
CREATE TABLE "public"."cache_top100_alltime" (
    "callsign" text,
    "flighttime" interval,
    "rank" integer,
    "lastweek" interval,
    "last30days" interval,
    "effective_flight_time" interval,
    "effective_lastweek" interval,
    "effective_last30days" interval
) WITH (oids = false);


DROP TABLE IF EXISTS "fgms_servers";
CREATE TABLE "public"."fgms_servers" (
    "name" text NOT NULL,
    "ip" text NOT NULL,
    "key" text NOT NULL,
    "maintainer" text,
    "location" text,
    "email" text,
    "receive_email" boolean NOT NULL,
    "last_comm" timestamptz,
    "enabled" boolean NOT NULL
) WITH (oids = false);


DROP TABLE IF EXISTS "fixes";
CREATE SEQUENCE fixes_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."fixes" (
    "id" integer DEFAULT nextval('fixes_id_seq') NOT NULL,
    "latitude" double precision,
    "longitude" double precision,
    "name" text NOT NULL,
    CONSTRAINT "fix_name" PRIMARY KEY ("name"),
    CONSTRAINT "fixes-id-idx" UNIQUE ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "flight_plans";
CREATE TABLE "public"."flight_plans" (
    "id" integer,
    "seq" integer,
    "fix_name" text
) WITH (oids = false);

CREATE INDEX "flight_plans-id-idx" ON "public"."flight_plans" USING btree ("id");

CREATE INDEX "flight_plans-seq-idx" ON "public"."flight_plans" USING btree ("seq");


DROP TABLE IF EXISTS "flights";
CREATE SEQUENCE flights_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."flights" (
    "id" integer DEFAULT nextval('flights_id_seq') NOT NULL,
    "callsign" text,
    "status" text,
    "model" text,
    "start_time" timestamptz,
    "end_time" timestamptz,
    "effective_flight_time" integer,
    "start_icao" character(4),
    "end_icao" character(4),
    "server" text,
    CONSTRAINT "flights_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

CREATE INDEX "flights-callsign-idx" ON "public"."flights" USING btree ("callsign");


DROP TABLE IF EXISTS "flights_archive";
CREATE TABLE "public"."flights_archive" (
    "id" integer,
    "callsign" text,
    "status" text,
    "model" text,
    "start_time" timestamptz,
    "end_time" timestamptz,
    "effective_flight_time" integer,
    "wpts" integer,
    "start_icao" character(4),
    "end_icao" character(4),
    CONSTRAINT "flights_archive2_pkey" UNIQUE ("id")
) WITH (oids = false);

CREATE INDEX "flights_archive-callsign-idx" ON "public"."flights_archive" USING btree ("callsign");


DROP TABLE IF EXISTS "geo_airports";
CREATE TABLE "public"."geo_airports" (
    "icao" character varying(4) NOT NULL,
    "name" character varying(80) NOT NULL,
    "lat" double precision,
    "lon" double precision,
    "alt" double precision,
    "city" character varying(80),
    "country" character(2),
    "modify_date" timestamptz,
    "admin_area_lv_1" character varying(60),
    "admin_area_lv_2" character varying(60),
    "admin_area_lv_3" character varying(60),
    "admin_area_lv_4" character varying(60),
    "airport_type" smallint,
    CONSTRAINT "geo_airport_icao_key" UNIQUE ("icao")
) WITH (oids = false);

COMMENT ON COLUMN "public"."geo_airports"."airport_type" IS '100 = Land airport; 101=sea airport; 102=Heliport';


DROP TABLE IF EXISTS "log";
CREATE TABLE "public"."log" (
    "username" text,
    "table" text,
    "action" text NOT NULL,
    "when" timestamptz,
    "callsign" text,
    "usercomments" text,
    "flight_id" integer,
    "flight_id2" integer
) WITH (oids = false);


DROP TABLE IF EXISTS "models";
CREATE TABLE "public"."models" (
    "fg_string" text NOT NULL,
    "human_string" text,
    CONSTRAINT "models_pkey" PRIMARY KEY ("fg_string")
) WITH (oids = false);


DROP TABLE IF EXISTS "navaid_types";
CREATE TABLE "public"."navaid_types" (
    "id" integer NOT NULL,
    "label" text,
    CONSTRAINT "navaid_types_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "navaids";
CREATE SEQUENCE navaids_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."navaids" (
    "id" integer DEFAULT nextval('navaids_id_seq') NOT NULL,
    "type" integer,
    "latitude" double precision,
    "longitude" double precision,
    "range" integer,
    "freq" double precision,
    "ilt" text,
    "name" text
) WITH (oids = false);

CREATE INDEX "navaids-id-idx" ON "public"."navaids" USING btree ("id");

CREATE INDEX "navaids-ilt-idx" ON "public"."navaids" USING btree ("ilt");

CREATE INDEX "navaids-lat_lon-idx" ON "public"."navaids" USING btree ("latitude", "longitude");

CREATE INDEX "navaids_type_idx" ON "public"."navaids" USING btree ("type");


DROP TABLE IF EXISTS "pilot_request";
CREATE TABLE "public"."pilot_request" (
    "username" text,
    "callsign" text NOT NULL,
    "request" text NOT NULL,
    "flight_id" integer NOT NULL,
    "flight_id2" integer,
    "request_time" timestamptz,
    "status" text
) WITH (oids = false);


DROP TABLE IF EXISTS "route_points";
CREATE TABLE "public"."route_points" (
    "route_id" integer,
    "seq" integer,
    "navaid_id" integer
) WITH (oids = false);

CREATE INDEX "route_points-route_id-idx" ON "public"."route_points" USING btree ("route_id");

CREATE INDEX "route_points-seq-idx" ON "public"."route_points" USING btree ("seq");


DROP TABLE IF EXISTS "routes";
CREATE SEQUENCE routes_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."routes" (
    "id" integer DEFAULT nextval('routes_id_seq') NOT NULL,
    "status" text,
    "dep_airport_id" integer,
    "dep_runway" text,
    "arr_airport_id" integer,
    "arr_runway" text,
    "plan_date" timestamp,
    "types" text,
    "mind" real,
    "maxd" real
) WITH (oids = false);

CREATE INDEX "routes-arr-idx" ON "public"."routes" USING btree ("arr_airport_id", "arr_runway");

CREATE INDEX "routes-dep-idx" ON "public"."routes" USING btree ("dep_airport_id", "dep_runway");

CREATE INDEX "routes-id-idx" ON "public"."routes" USING btree ("id");

CREATE INDEX "routes-limits-idx" ON "public"."routes" USING btree ("types", "mind", "maxd");

CREATE INDEX "routes-status-idx" ON "public"."routes" USING btree ("status");


DROP TABLE IF EXISTS "runways";
CREATE TABLE "public"."runways" (
    "airport_id" integer,
    "runway" text,
    "latitude" double precision,
    "longitude" double precision,
    "llz_freq" double precision,
    "llz_ilt" text,
    "llz_name" text
) WITH (oids = false);

CREATE INDEX "runways-airport_id-idx" ON "public"."runways" USING btree ("airport_id");


DROP TABLE IF EXISTS "temp_cache_top100_alltime";
CREATE SEQUENCE temp_cache_top100_alltime_rank_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."temp_cache_top100_alltime" (
    "callsign" text,
    "flighttime" interval,
    "effective_flight_time" interval,
    "rank" integer DEFAULT nextval('temp_cache_top100_alltime_rank_seq') NOT NULL
) WITH (oids = false);


DROP TABLE IF EXISTS "temp_flights";
CREATE SEQUENCE flights_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 START 1 CACHE 1;

CREATE TABLE "public"."temp_flights" (
    "id" integer DEFAULT nextval('flights_id_seq') NOT NULL,
    "callsign" text,
    "status" text,
    "model" text,
    "start_time" timestamptz,
    "end_time" timestamptz,
    "distance" double precision,
    "max_altimeter" double precision,
    "max_speed" double precision,
    CONSTRAINT "temp_flights_pkey" PRIMARY KEY ("id")
) WITH (oids = false);


DROP TABLE IF EXISTS "tracker_stats";
CREATE TABLE "public"."tracker_stats" (
    "month" text NOT NULL,
    "count" integer,
    "modified" timestamp,
    CONSTRAINT "tracker_stats_pkey" PRIMARY KEY ("month")
) WITH (oids = false);


DROP TABLE IF EXISTS "waypoints";
CREATE TABLE "public"."waypoints" (
    "flight_id" integer NOT NULL,
    "time" timestamptz NOT NULL,
    "latitude" double precision NOT NULL,
    "longitude" double precision NOT NULL,
    "altitude" double precision NOT NULL,
    "heading" double precision,
    CONSTRAINT "waypoints-pkey" PRIMARY KEY ("flight_id", "time")
) WITH (oids = false);

CREATE INDEX "waypoints_time_flight_id-idx" ON "public"."waypoints" USING btree ("time", "flight_id");


DROP TABLE IF EXISTS "waypoints_archive";
CREATE TABLE "public"."waypoints_archive" (
    "flight_id" integer NOT NULL,
    "time" timestamptz NOT NULL,
    "latitude" double precision NOT NULL,
    "longitude" double precision NOT NULL,
    "altitude" double precision NOT NULL,
    "heading" double precision
) WITH (oids = false);

CREATE INDEX "waypoints_archive_flight_id-idx" ON "public"."waypoints_archive" USING btree ("flight_id");


DROP VIEW IF EXISTS "flights_all";
CREATE TABLE "flights_all" ("id" integer, "callsign" text, "status" text, "model" text, "start_time" timestamptz, "end_time" timestamptz, "effective_flight_time" integer, "start_icao" character(4), "end_icao" character(4), "wpts" integer, "table" text);


DROP TABLE IF EXISTS "callsigns";
CREATE TABLE "public"."callsigns" (
    "callsign" character varying(7) NOT NULL,
    "email" character varying NOT NULL,
    "reg_ip" cidr NOT NULL,
    "reg_token" character varying NOT NULL,
    "reg_time" timestamptz DEFAULT statement_timestamp() NOT NULL,
    "comments" text,
    "activation_level" smallint DEFAULT (0) NOT NULL,
    CONSTRAINT "callsigns_callsign" PRIMARY KEY ("callsign")
) WITH (oids = false);

COMMENT ON COLUMN "public"."callsigns"."activation_level" IS '-3 = Dispute; -2 = Protected; -1 = Deactivated; 0 = Registered; 10 = Activated';


DROP VIEW IF EXISTS "waypoints_all";
CREATE TABLE "waypoints_all" ("flight_id" integer, "time" timestamptz, "latitude" double precision, "longitude" double precision, "altitude" double precision, "heading" double precision);


DROP TABLE IF EXISTS "flights_all";
CREATE TABLE "public"."flights_all" (
    "id" integer,
    "callsign" text,
    "status" text,
    "model" text,
    "start_time" timestamptz,
    "end_time" timestamptz,
    "effective_flight_time" integer,
    "start_icao" character(4),
    "end_icao" character(4),
    "wpts" integer,
    "table" text
) WITH (oids = false);

DROP TABLE IF EXISTS "waypoints_all";
CREATE TABLE "public"."waypoints_all" (
    "flight_id" integer,
    "time" timestamptz,
    "latitude" double precision,
    "longitude" double precision,
    "altitude" double precision,
    "heading" double precision
) WITH (oids = false);

-- 2017-05-04 10:00:18.021575+08
