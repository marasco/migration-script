// Remove anything which isn't a word, whitespace, number
// or any of the following caracters -_~,;[]().
// If you don't need to handle multi-byte characters
// you can use preg_replace rather than mb_ereg_replace
// Thanks @Łukasz Rysiak!
$file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
// Remove any runs of periods (thanks falstro!)
$file = mb_ereg_replace("([\.]{2,})", '', $file);