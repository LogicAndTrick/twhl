DROP FUNCTION IF EXISTS unesc;

CREATE FUNCTION unesc (value TEXT)
RETURNS TEXT
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
    '&amp;', '&');