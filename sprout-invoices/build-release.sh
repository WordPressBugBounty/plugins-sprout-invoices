#!/usr/bin/env bash
#
# build-release.sh — Manual replacement for the broken plugin-build script.
#
# Usage:
#   ./build-release.sh <version> [edition ...]
#
# Editions: free pro biz corp collab  (default: all five)
#
# Examples:
#   ./build-release.sh 20.8.11               # builds all editions
#   ./build-release.sh 20.8.11 free          # free only
#   ./build-release.sh 20.8.11 pro biz corp  # three pro tiers
#
# Output lands in ./dist/

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="$SCRIPT_DIR/build"
DIST_DIR="$SCRIPT_DIR/dist"
TMP_DIR="$BUILD_DIR/tmp"

# ── args ─────────────────────────────────────────────────────────────────────

VERSION="${1:-}"
if [ -z "$VERSION" ]; then
    echo "Usage: $0 <version> [free|pro|biz|corp|collab ...]"
    exit 1
fi

shift
EDITIONS=("${@:-free pro biz corp collab}")
if [ "${#EDITIONS[@]}" -eq 0 ] || [ "${EDITIONS[0]}" = "free pro biz corp collab" ]; then
    EDITIONS=(free pro biz corp collab)
fi

# ── helpers ──────────────────────────────────────────────────────────────────

# apply_filters <target_dir> <edition>
# Reads filter-all and filter-<edition>, deletes every listed path from
# <target_dir>.  Handles globs (e.g. /screenshot*.png, /bundles/si*).
apply_filters() {
    local target="$1"
    local edition="$2"

    local filter_files=("$BUILD_DIR/filter-all" "$BUILD_DIR/filter-$edition")

    for filter_file in "${filter_files[@]}"; do
        [ -f "$filter_file" ] || { echo "  WARNING: $filter_file not found, skipping."; continue; }

        while IFS= read -r line || [ -n "$line" ]; do
            # strip whitespace
            line="${line#"${line%%[![:space:]]*}"}"
            line="${line%"${line##*[![:space:]]}"}"

            # only process "- /path" lines
            [[ "$line" == "- "* ]] || continue
            local rel="${line#- }"   # e.g. /sprout-invoices-pro.php  or /screenshot*.png
            rel="${rel#/}"           # strip leading slash

            # expand globs against the target dir (nullglob = no match → skip)
            shopt -s nullglob
            local matches=("$target"/$rel)
            shopt -u nullglob

            for f in "${matches[@]}"; do
                rm -rf "$f"
            done
        done < "$filter_file"
    done
}

# ── per-edition config ────────────────────────────────────────────────────────

edition_inner_dir() {
    case "$1" in
        free)         echo "sprout-invoices" ;;
        pro|biz|corp) echo "sprout-invoices-pro" ;;
        collab)       echo "sprout-invoices-pro-collab" ;;
    esac
}

edition_zip_name() {
    case "$1" in
        free)   echo "sprout-invoices-$VERSION.zip" ;;
        pro)    echo "sprout-invoices-freelancer-$VERSION.zip" ;;
        biz)    echo "sprout-invoices-business-$VERSION.zip" ;;
        corp)   echo "sprout-invoices-corprate-$VERSION.zip" ;;
        collab) echo "sprout-invoices-pro-collab.zip" ;;
    esac
}

# ── build one edition ─────────────────────────────────────────────────────────

build_edition() {
    local edition="$1"
    local inner
    inner="$(edition_inner_dir "$edition")"
    local zip_name
    zip_name="$(edition_zip_name "$edition")"
    local work_dir="$TMP_DIR/$edition/$inner"

    echo "▶ Building $edition → $zip_name"

    mkdir -p "$work_dir"

    # Copy everything except .git into the inner dir
    rsync -r --exclude='.git' --exclude='build/tmp' "$SCRIPT_DIR/" "$work_dir/"

    # Delete files per filter-all + filter-<edition>
    apply_filters "$work_dir" "$edition"

    # Zip (inner dir name becomes the folder inside the zip)
    local zip_path="$DIST_DIR/$zip_name"
    rm -f "$zip_path"
    ( cd "$TMP_DIR/$edition" && zip -r "$zip_path" "$inner" -q )

    rm -rf "$TMP_DIR/$edition"
    echo "  ✓ $zip_path"
}

# ── main ──────────────────────────────────────────────────────────────────────

mkdir -p "$DIST_DIR" "$TMP_DIR"

for edition in "${EDITIONS[@]}"; do
    build_edition "$edition"
done

rm -rf "$TMP_DIR"

echo ""
echo "Done. Zips in $DIST_DIR/"
