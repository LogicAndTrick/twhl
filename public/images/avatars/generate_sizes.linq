<Query Kind="Statements">
  <Namespace>System.Drawing</Namespace>
</Query>

var cwd = Environment.CurrentDirectory;
var dir = Path.Combine(cwd, "full");
foreach (var f in Directory.GetFiles(dir))
{
	var img = new Bitmap(f);
	var s45 = new Bitmap(45, 45);
	using (var g = Graphics.FromImage(s45)) {
		g.CompositingQuality = System.Drawing.Drawing2D.CompositingQuality.HighQuality;
		g.SmoothingMode = System.Drawing.Drawing2D.SmoothingMode.AntiAlias;
		g.CompositingMode = System.Drawing.Drawing2D.CompositingMode.SourceCopy;
		g.InterpolationMode = System.Drawing.Drawing2D.InterpolationMode.HighQualityBicubic;
		g.DrawImage(img, 0, 0, 45, 45);
	}
	var s20 = new Bitmap(20, 20);
	using (var g = Graphics.FromImage(s20)) {
		g.CompositingQuality = System.Drawing.Drawing2D.CompositingQuality.HighQuality;
		g.SmoothingMode = System.Drawing.Drawing2D.SmoothingMode.AntiAlias;
		g.CompositingMode = System.Drawing.Drawing2D.CompositingMode.SourceCopy;
		g.InterpolationMode = System.Drawing.Drawing2D.InterpolationMode.HighQualityBicubic;
		g.DrawImage(img, 0, 0, 20, 20);
	}
	
	var f45 = Path.Combine(cwd, "small", Path.GetFileName(f));
	var f20 = Path.Combine(cwd, "inline", Path.GetFileName(f));
	
	if (File.Exists(f45)) File.Delete(f45);
	if (File.Exists(f20)) File.Delete(f20);
	
	s45.Save(f45);
	s20.Save(f20);
}