# Extracts "path|line|sniff|token" from `phpcs --report=json` on stdin.
#
# Notes:
# - Bind fields to variables first: inside a pipeline, `//` operates on the
#   piped value, not the original message object.
# - split() rejects a null separator, so every index is coalesced to "" before
#   the next split runs; "" is a valid string and yields no match.
.files
| to_entries[]
| select(.value | type == "object")
| select(.value.messages? != null)
| .key as $file
| .value.messages[]
| .message as $msg
| [
    ($file | ltrimstr($root)),
    (.line | tostring),
    .source,
    (
      ($msg | split("found ") | (.[1] // "") | split("'") | (.[1] // ""))
      // ($msg | split("'") | (.[1] // ""))
    )
  ]
| join("|")
