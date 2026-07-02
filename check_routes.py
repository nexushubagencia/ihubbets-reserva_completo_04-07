import os, re
def get_routes(path):
    routes = set()
    if not os.path.exists(path): return routes
    with open(path, 'r', encoding='utf-8', errors='ignore') as f:
        for line in f:
            m = re.search(r'Route::(get|post|put|delete|any)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]', line, re.IGNORECASE)
            if not m:
                m = re.search(r'->(get|post|put|delete|any)\s*\(\s*[\'\"]([^\'\"]+)[\'\"]', line, re.IGNORECASE)
            if m:
                method, route = m.groups()
                routes.add(method.upper() + ' ' + route.strip('/'))
    return routes

l_web = get_routes(r'd:\IHUB BETS V2.1.0-SISTEMA ESPORTIVO\legacy-source\sistema\routes\web.php')
l_api = get_routes(r'd:\IHUB BETS V2.1.0-SISTEMA ESPORTIVO\legacy-source\sistema\routes\api.php')
v_web = get_routes(r'd:\IHUB BETS V2.1.0-SISTEMA ESPORTIVO\IHUB BETS-V2.1.0\routes\web.php')
v_api = get_routes(r'd:\IHUB BETS V2.1.0-SISTEMA ESPORTIVO\IHUB BETS-V2.1.0\routes\api.php')

# For web routes, V2 might prefix with admin/ so we check suffix
missing_web = []
for lr in l_web:
    method, path = lr.split(' ', 1)
    found = False
    for vr in v_web:
        vm, vp = vr.split(' ', 1)
        if vm == method and (vp == path or vp.endswith('/' + path) or path.endswith('/' + vp) or path in vp or vp in path):
            found = True
            break
    if not found:
        missing_web.append(lr)

missing_api = []
for lr in l_api:
    method, path = lr.split(' ', 1)
    found = False
    for vr in v_api:
        vm, vp = vr.split(' ', 1)
        if vm == method and (vp == path or vp.endswith('/' + path) or path.endswith('/' + vp) or path in vp or vp in path):
            found = True
            break
    if not found:
        missing_api.append(lr)

print('--- WEB MISSING ---')
for r in sorted(missing_web): print(r)
print('\n--- API MISSING ---')
for r in sorted(missing_api): print(r)
