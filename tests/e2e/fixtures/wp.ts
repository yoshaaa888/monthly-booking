import { execSync } from 'node:child_process';

function run(cmd: string) {
  return execSync(cmd, { stdio: 'pipe', encoding: 'utf-8', cwd: `${__dirname}/../../..` }).trim();
}

export function wpDbQuery(sql: string): string {
  const safe = sql.replace(/"/g, '\\"');
  const cmd = `docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "${safe}"`;
  return run(cmd);
}

export function wpScalar(sql: string): number {
  const out = wpDbQuery(sql);
  const m = out.match(/^\s*(\d+)\s*$/m);
  if (m) return parseInt(m[1], 10);
  const num = parseInt(out, 10);
  if (!isNaN(num)) return num;
  return 0;
}
