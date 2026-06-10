# Synapse NLP Fine-Tuning Guide

This guide walks through fine-tuning a local LLM on your Synapse conversation data.

## Prerequisites

- Python 3.10+
- GPU with 12GB+ VRAM (or use Google Colab free tier)
- [Unsloth](https://github.com/unslothai/unsloth) for efficient fine-tuning

## Step 1: Export Training Data

```bash
php artisan ai:export-training-data --include-examples
```

Output: `storage/app/training/synapse-training.jsonl`

## Step 2: Fine-Tune with Unsloth

```python
# install: pip install unsloth
from unsloth import FastLanguageModel
from unsloth import is_bfloat16_supported
import torch
from datasets import load_dataset
from trl import SFTTrainer
from transformers import TrainingArguments

model_name = "unsloth/Mistral-7B-Instruct-v0.3-bnb-4bit"

model, tokenizer = FastLanguageModel.from_pretrained(
    model_name=model_name,
    max_seq_length=4096,
    dtype=None,
    load_in_4bit=True,
)

model = FastLanguageModel.get_peft_model(
    model,
    r=16,
    target_modules=["q_proj", "k_proj", "v_proj", "o_proj",
                    "gate_proj", "up_proj", "down_proj"],
    lora_alpha=16,
    lora_dropout=0,
    bias="none",
    use_gradient_checkpointing="unsloth",
    random_state=42,
)

dataset = load_dataset("json", data_files="synapse-training.jsonl", split="train")

def format_chat(example):
    text = tokenizer.apply_chat_template(
        example["messages"], tokenize=False, add_generation_prompt=False
    )
    return {"text": text}

dataset = dataset.map(format_chat)

trainer = SFTTrainer(
    model=model,
    tokenizer=tokenizer,
    train_dataset=dataset,
    dataset_text_field="text",
    max_seq_length=4096,
    args=TrainingArguments(
        per_device_train_batch_size=2,
        gradient_accumulation_steps=4,
        warmup_steps=5,
        max_steps=60,
        learning_rate=2e-4,
        fp16=not is_bfloat16_supported(),
        bf16=is_bfloat16_supported(),
        logging_steps=1,
        output_dir="outputs",
    ),
)

trainer.train()
model.save_pretrained("synapse-nlp")
tokenizer.save_pretrained("synapse-nlp")
```

## Step 3: Export to Ollama

```bash
# Convert to GGUF and import into Ollama
python unsloth/convert.py synapse-nlp --gguf synapse-nlp.gguf

# Create Ollama Modelfile
echo "FROM ./synapse-nlp.gguf
TEMPLATE \"{{ .Prompt }}\"
PARAMETER temperature 0.1" > Modelfile

ollama create synapse-nlp -f Modelfile
```

## Step 4: Configure Synapse

```bash
# In .env
AI_PROVIDER=local
AI_LOCAL_ENDPOINT=http://localhost:11434
AI_LOCAL_MODEL=synapse-nlp:latest
```

## Quick Alternative: Use Ollama + Any Base Model (No Fine-Tuning)

```bash
# Just use a good base model directly
ollama pull mistral:7b

# In .env
AI_PROVIDER=local
AI_LOCAL_MODEL=mistral:7b
```

## Verify

```bash
php artisan tinker
> app(LocalAiService::class)->isAvailable()
=> true
```
