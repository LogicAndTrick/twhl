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