DROP FUNCTION IF EXISTS unesc;

CREATE FUNCTION unesc (
    value TEXT CHARSET utf8mb4
)
RETURNS TEXT CHARSET utf8mb4
DETERMINISTIC
NO SQL
SQL SECURITY DEFINER
RETURN
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    value,
    '&quot;', '"' ),
    '&#39;', CHAR(39)),
    '&lt;', '<' ),
    '&gt;', '>' ),
    '&amp;', '&') COLLATE utf8mb4_unicode_ci;

DROP FUNCTION IF EXISTS ts;

CREATE FUNCTION ts (
    stamp INT
) RETURNS TIMESTAMP
NO SQL
SQL SECURITY DEFINER
RETURN
    CONVERT_TZ(FROM_UNIXTIME(stamp), '+08:00', '+00:00');

/*
Convert from local time to UTC:
TWHL3 stored dates in sever time (+8:00)
so we need to convert it back to UTC first.
*/