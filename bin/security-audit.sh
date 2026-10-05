#!/usr/bin/env bash
#
# Security audit for the views/ directory — before/after comparison.
#
#   ./bin/security-audit.sh before   # snapshot current findings as the baseline
#   ./bin/security-audit.sh after    # diff current findings against the baseline
#   ./bin/security-audit.sh show     # list current findings, no baseline needed
#
# Findings are keyed by file + sniff + the variable/function that triggered it,
# NOT by line number — so a fix that shifts lines down does not read as a
# regression.
#
# Uses the PHPCS already installed in vendor/. No new dependencies.

set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT" || exit 1

PHPCS="./vendor/bin/phpcs"
BASELINE="bin/security-audit-baseline.txt"
TARGET="views"

# Output-escaping, input-sanitising, SQL, nonce/late-escaping, extract(), WP global overrides.
# Note: PHPCS has no unserialize sniff; the raw @unserialize() call in
# views/quiz/attempt-details.php is tracked in the plan, not by the scanner.
SNIFFS="WordPress.Security.EscapeOutput,WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification,WordPress.DB.PreparedSQL,WordPress.DB.PreparedSQLPlaceholders,WordPress.Security.LateEscaping,WordPress.PHP.DontExtract,WordPress.WP.GlobalVariablesOverride"

[ -x "$PHPCS" ] || { echo "phpcs not found. Run: composer install" >&2; exit 1; }

# Emit "path|line|sniff|token" for every finding.
# --ignore-annotations is the point of the whole script: it ignores the
# phpcs:ignore comments that otherwise hide most of views' real findings.
collect() {
	"$PHPCS" --standard=WordPress --sniffs="$SNIFFS" --ignore-annotations \
		--report=json "$TARGET" 2>/dev/null \
	| jq -r --arg root "$ROOT/" -f bin/security-audit.jq 2>/dev/null | sort -t'|' -k1,1 -k3,3 -k4,4
}

# Drop line number so keys survive edits that shift code down the file.
collect_stable() {
	collect | awk -F'|' '{print $1 "|" $3 "|" $4}' | sort -u
}

case "${1:-show}" in
	before)
		collect_stable > "$BASELINE"
		echo "baseline written: $BASELINE ($(wc -l < "$BASELINE" | tr -d ' ') unique findings)"
		;;

	after)
		[ -f "$BASELINE" ] || { echo "no baseline. Run: $0 before" >&2; exit 1; }
		cur="$(mktemp)"
		collect_stable > "$cur"

		new=$(comm -13 "$BASELINE" "$cur" | wc -l | tr -d ' ')
		fixed=$(comm -23 "$BASELINE" "$cur" | wc -l | tr -d ' ')

		echo "baseline: $(wc -l < "$BASELINE" | tr -d ' ')  current: $(wc -l < "$cur" | tr -d ' ')  |  resolved: $fixed  introduced: $new"
		echo

		[ "$fixed" -gt 0 ] && { echo "RESOLVED:"; comm -23 "$BASELINE" "$cur" | sed 's/^/  - /'; echo; }
		[ "$new" -gt 0 ] && { echo "NEW (fix or justify each):"; comm -13 "$BASELINE" "$cur" | sed 's/^/  + /'; echo; }

		rm -f "$cur"
		[ "$new" -eq 0 ] || exit 1
		;;

	show)
		collect
		;;

	*)
		echo "usage: $0 {before|after|show}" >&2
		exit 1
		;;
esac
