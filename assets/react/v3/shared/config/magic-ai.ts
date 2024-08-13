import type { Option } from "@Utils/types";



export type ChatTone = 'formal' | 'casual' | 'professional' | 'enthusiastic' | 'informational' | 'funny';
export type ChatFormat = 'essay' | 'paragraph' | 'email' | 'idea' | 'blog-post' | 'outline' | 'comment';
export type ChatLength = 'short' | 'medium' | 'large';
export type ChatLanguage =
  | 'english'
  | 'simplified-chinese'
  | 'traditional-chinese'
  | 'spanish'
  | 'french'
  | 'japanese'
  | 'german'
  | 'portuguese'
  | 'arabic'
  | 'russian'
  | 'italian'
  | 'korean'
  | 'hindi'
  | 'dutch'
  | 'polish'
  | 'amharic'
  | 'bulgarian'
  | 'bengali'
  | 'czech'
  | 'danish'
  | 'greek'
  | 'estonian'
  | 'persian'
  | 'filipino'
  | 'croatian'
  | 'hungarian'
  | 'indonesian'
  | 'lithuanian'
  | 'latvian'
  | 'malay'
  | 'norwegian'
  | 'romanian'
  | 'slovak'
  | 'slovenian'
  | 'serbian'
  | 'swedish'
  | 'thai'
  | 'turkish'
  | 'ukrainian'
  | 'urdu'
  | 'vietnamese';
const languageOptions: Option<ChatLanguage>[] = [
  { label: 'English', value: 'english' },
  { label: '简体中文', value: 'simplified-chinese' },
  { label: '繁體中文', value: 'traditional-chinese' },
  { label: 'Español', value: 'spanish' },
  { label: 'Français', value: 'french' },
  { label: '日本語', value: 'japanese' },
  { label: 'Deutsch', value: 'german' },
  { label: 'Português', value: 'portuguese' },
  { label: 'العربية', value: 'arabic' },
  { label: 'Русский', value: 'russian' },
  { label: 'Italiano', value: 'italian' },
  { label: '한국어', value: 'korean' },
  { label: 'हिन्दी', value: 'hindi' },
  { label: 'Nederlands', value: 'dutch' },
  { label: 'Polski', value: 'polish' },
  { label: 'አማርኛ', value: 'amharic' },
  { label: 'Български', value: 'bulgarian' },
  { label: 'বাংলা', value: 'bengali' },
  { label: 'Čeština', value: 'czech' },
  { label: 'Dansk', value: 'danish' },
  { label: 'Ελληνικά', value: 'greek' },
  { label: 'Eesti', value: 'estonian' },
  { label: 'فارسی', value: 'persian' },
  { label: 'Filipino', value: 'filipino' },
  { label: 'Hrvatski', value: 'croatian' },
  { label: 'Magyar', value: 'hungarian' },
  { label: 'Bahasa Indonesia', value: 'indonesian' },
  { label: 'Lietuvių', value: 'lithuanian' },
  { label: 'Latviešu', value: 'latvian' },
  { label: 'Melayu', value: 'malay' },
  { label: 'Norsk', value: 'norwegian' },
  { label: 'Română', value: 'romanian' },
  { label: 'Slovenčina', value: 'slovak' },
  { label: 'Slovenščina', value: 'slovenian' },
  { label: 'Српски', value: 'serbian' },
  { label: 'Svenska', value: 'swedish' },
  { label: 'ภาษาไทย', value: 'thai' },
  { label: 'Türkçe', value: 'turkish' },
  { label: 'Українська', value: 'ukrainian' },
  { label: 'اردو', value: 'urdu' },
  { label: 'Tiếng Việt', value: 'vietnamese' },
];

const toneOptions: Option<ChatTone>[] = [
  { label: 'Formal', value: 'formal' },
  { label: 'Casual', value: 'casual' },
  { label: 'Professional', value: 'professional' },
  { label: 'Enthusiastic', value: 'enthusiastic' },
  { label: 'Informational', value: 'informational' },
  { label: 'Funny', value: 'funny' },
];

const formatOptions: Option<ChatFormat>[] = [
  { label: 'Essay', value: 'essay' },
  { label: 'Paragraph', value: 'paragraph' },
  { label: 'Email', value: 'email' },
  { label: 'Idea', value: 'idea' },
  { label: 'Blog Post', value: 'blog-post' },
  { label: 'Outline', value: 'outline' },
  { label: 'Comment', value: 'comment' },
];

export { formatOptions, languageOptions, toneOptions };

