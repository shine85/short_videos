#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SITE_ROOT="${SITE_ROOT:-/www/wwwroot/api2.jumh989.gq}"
OVERRIDE_DIR="${OVERRIDE_DIR:-/www/backup/api2-short-videos-local}"

FILES=(
  "short_videos/sv1.php"
  "short_videos/sv2.php"
)

usage() {
  cat <<EOF
用法:
  bash deploy/baota-local-overrides.sh init
  bash deploy/baota-local-overrides.sh apply
  bash deploy/baota-local-overrides.sh status

可选环境变量:
  SITE_ROOT=/www/wwwroot/api2.jumh989.gq
  OVERRIDE_DIR=/www/backup/api2-short-videos-local
EOF
}

ensure_file_exists() {
  local file_path="$1"
  if [[ ! -f "${file_path}" ]]; then
    echo "文件不存在: ${file_path}" >&2
    exit 1
  fi
}

copy_with_parent() {
  local source_file="$1"
  local target_file="$2"
  mkdir -p "$(dirname "${target_file}")"
  cp "${source_file}" "${target_file}"
}

init_overrides() {
  echo "初始化本地覆盖备份..."
  for relative_path in "${FILES[@]}"; do
    local source_file="${SITE_ROOT}/${relative_path}"
    local backup_file="${OVERRIDE_DIR}/${relative_path}"
    ensure_file_exists "${source_file}"
    copy_with_parent "${source_file}" "${backup_file}"
    echo "已备份 ${relative_path} -> ${backup_file}"
  done
}

apply_overrides() {
  echo "应用本地覆盖文件..."
  for relative_path in "${FILES[@]}"; do
    local backup_file="${OVERRIDE_DIR}/${relative_path}"
    local target_file="${SITE_ROOT}/${relative_path}"
    ensure_file_exists "${backup_file}"
    copy_with_parent "${backup_file}" "${target_file}"
    echo "已恢复 ${relative_path} -> ${target_file}"
  done
}

show_status() {
  echo "SITE_ROOT=${SITE_ROOT}"
  echo "OVERRIDE_DIR=${OVERRIDE_DIR}"
  for relative_path in "${FILES[@]}"; do
    local source_file="${SITE_ROOT}/${relative_path}"
    local backup_file="${OVERRIDE_DIR}/${relative_path}"
    if [[ -f "${source_file}" ]]; then
      echo "[线上存在] ${source_file}"
    else
      echo "[线上缺失] ${source_file}"
    fi

    if [[ -f "${backup_file}" ]]; then
      echo "[备份存在] ${backup_file}"
    else
      echo "[备份缺失] ${backup_file}"
    fi
  done
}

main() {
  local action="${1:-}"
  case "${action}" in
    init)
      init_overrides
      ;;
    apply)
      apply_overrides
      ;;
    status)
      show_status
      ;;
    *)
      usage
      exit 1
      ;;
  esac
}

main "$@"
