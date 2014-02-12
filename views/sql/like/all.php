SELECT SQL_NO_CACHE p.post id,
  MAX(p.plike) `like`,
  MAX(p.pmlike) `mlike`,
  MAX(p.pdislike) `dislike`,
  CASE IFNULL(ulike.type,3)
    WHEN 0 THEN 'like'
    WHEN 1 THEN 'mlike'
    WHEN 2 THEN 'dislike'
    ELSE NULL
  END user
  FROM (
    SELECT SQL_NO_CACHE
      likes.*,
      IF(@lpost!=likes.post,IF(likes.type=0,@like:=1,@like:=0),IF(likes.type=0,@like:=@like+1,@like)) plike,
      IF(@mpost!=likes.post,IF(likes.type=1,@mlike:=1,@mlike:=0),IF(likes.type=1,@mlike:=@mlike+1,@mlike)) pmlike,
      IF(@dpost!=likes.post,IF(likes.type=2,@dislike:=1,@dislike:=0),IF(likes.type=2,@dislike:=@dislike+1,@dislike)) pdislike,
      @lpost:=likes.post post1,
      @mpost:=likes.post post2,
      @dpost:=likes.post post3
      FROM <?php echo $prefix?>likes likes, (SELECT @like:=0,@lpost:=0) l, (SELECT @mlike:=0,@mpost:=0) m, (SELECT @dislike:=0,@dpost:=0) d
      WHERE likes.post IN (?q)
      ORDER BY likes.post
  ) p
  LEFT JOIN <?php echo $prefix?>likes ulike ON ulike.user = ?i AND ulike.post = p.post
  GROUP BY p.post
  
