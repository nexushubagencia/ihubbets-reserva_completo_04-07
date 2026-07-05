import re

src = r'E:\SISTEMA BET PRO\cassinos\cassino-v3\DB.sql'
dst = r'E:\SISTEMA BET PRO\SISTEMA IHUB\IHUB BETS-V2.1.0\IHUB BETS-V2.1.0\database\seeders\casino_v3_import.sql'

tables = {
    'categories': 'casino_categories',
    'providers': 'casino_providers',
    'games': 'casino_games',
    'category_game': 'casino_category_game',
}

out_lines = []
out_lines.append('SET FOREIGN_KEY_CHECKS=0;')
for orig, new in tables.items():
    out_lines.append('TRUNCATE TABLE {};'.format(new))
out_lines.append('SET FOREIGN_KEY_CHECKS=1;')

with open(src, 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()

for orig, new in tables.items():
    pattern = re.compile(r'INSERT INTO \`{}\` \([^)]+\) VALUES'.format(orig), re.IGNORECASE)
    for m in pattern.finditer(content):
        start = m.start()
        end = content.find(';', start)
        if end == -1:
            end = len(content)
        sql = content[start:end+1]
        sql = re.sub(r'INSERT INTO \`{}\`'.format(orig), 'INSERT INTO `{}`'.format(new), sql, flags=re.IGNORECASE)

        if orig == 'categories':
            sql = re.sub(
                r'\(`id`, `name`, `description`, `image`, `slug`, `url`, `created_at`, `updated_at`\)',
                '(`id`, `name`, `description`, `image`, `slug`, `created_at`, `updated_at`)',
                sql
            )
            def fix_categories_values(match):
                vals = match.group(1)
                parts = [p.strip() for p in vals.split(',')]
                if len(parts) == 8:
                    parts = parts[:5] + parts[6:]
                    return '(' + ', '.join(parts) + ')'
                return match.group(0)
            sql = re.sub(r'\(([^)]+)\)', fix_categories_values, sql)

        out_lines.append(sql)

with open(dst, 'w', encoding='utf-8') as f:
    f.write('\n'.join(out_lines))

print('SQL gerado:', dst)
