import re

def clean_file(path, replacements):
    with open(path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    for pattern, repl in replacements:
        content = re.sub(pattern, repl, content, flags=re.DOTALL)
        
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

match_replacements = [
    (r'(\s+)public function listLeagues\(\).*?(\s+public function (listLeaguesMain|getMatchesSearch|getModalities|getLiveMarketConfig))', r'\2'),
    (r'(\s+)public function listLeaguesMain\(\).*?(\s+public function (getMatchesSearch|getModalities|getLiveMarketConfig))', r'\2'),
    (r'(\s+)public function getModalities\(\).*?(\s+public function (getLiveMarketConfig|getMatches))', r'\2'),
    (r'(\s+)/\*\*\s+\*\s+\D+Busca liga por nome.*?\s+public function searchLeague\(Request \$request\).*?(\s+/\*\*|\s+public function searchTeam)', r'\2')
]

sports_replacements = [
    (r'(class SportsApiController extends ApiController\n\{).*?(    /\*\*\n     \* Rota genérica: /data/\{sport\}/\{day\})', r'\1\n\2'),
    (r'(\s+)public function getMatchesByDay\(.*?\).*?(\s+public function (getMatchesLive|listLeagues|getMatchesSearch|getModalities))', r'\2'),
    (r'(\s+)public function getMatchesLive\(\).*?(\s+public function (listLeagues|getMatchesSearch|getModalities))', r'\2'),
    (r'(\s+)public function getMatchesSearch\(.*?\).*?(\s+public function (getModalities|getLiveMarketConfig))', r'\2'),
    (r'(\s+)public function getMatches\(\).*?(\s+protected function applyFeaturedFormatting)', r'\2'),
    (r'(\s+)protected function applyFeaturedFormatting\(.*?\).*?(\s+public function getFeaturedMatches)', r'\2'),
    (r'(\s+)public function getFeaturedMatches\(\).*?(\s+public function getBanners)', r'\2'),
    (r'(\s+)public function getBanners\(\).*?(\s+/\*\*|\s+public function searchLeague)', r'\2'),
    (r'(\s+)/\*\*\s+\*\s+\D+Busca time por nome.*?\s+public function searchTeam\(Request \$request\).*?(\s+\})', r'\2')
]

clean_file('app/Http/Controllers/Api/MatchApiController.php', match_replacements)
clean_file('app/Http/Controllers/Api/SportsApiController.php', sports_replacements)
print("Cleanup complete!")
