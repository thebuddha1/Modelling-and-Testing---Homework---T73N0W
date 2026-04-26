<?php

namespace Tests\Feature\ModelBased;

class ListAdapter
{
    protected $test;

    protected $filterStreak = 0;
    protected $alphabetStreak = 0;
    protected $timeStreak = 0;
    protected $isSearchFilled = false;
    protected $isFiltered = false;

    protected $cards = [];
    protected $visible = [];

    protected $query = '';
    protected $filters = [];

    public function __construct($test)
    {
        $this->test = $test;

        $this->resetState();

        $this->cards = [
            $this->card('alpha', 100, 5, 0, 0),
            $this->card('beta', 200, 10, 1, 0),
            $this->card('gamma', 150, 2, 0, 1),
        ];

        $this->applyAll();
    }

    protected function resetState()
    {
        $this->filterStreak = 0;
        $this->alphabetStreak = 0;
        $this->timeStreak = 0;
        $this->isSearchFilled = false;
        $this->isFiltered = false;
    }

    protected function card($name, $updated, $likes, $reported, $disliked)
    {
        return (object)[
            'name' => strtolower($name),
            'updated' => $updated,
            'likes' => $likes,
            'reported' => $reported,
            'disliked' => $disliked
        ];
    }


    public function e_search()
    {
        if ($this->filterStreak >= 2 || $this->isSearchFilled) return;

        $this->query = 'a';
        $this->isSearchFilled = true;

        $this->timeStreak = 0;
        $this->alphabetStreak = 0;
        $this->filterStreak++;

        $this->applyAll();
    }

    public function e_filter()
    {
        if ($this->filterStreak >= 2 || $this->isFiltered) return;

        $this->filters[] = 'hide_disliked';
        $this->isFiltered = true;

        $this->timeStreak = 0;
        $this->alphabetStreak = 0;
        $this->filterStreak++;

        $this->applyAll();
    }

    public function e_deleteSearch()
    {
        if (!$this->isSearchFilled) return;

        $this->query = '';
        $this->isSearchFilled = false;

        $this->applyAll();
    }

    public function e_unfilter()
    {
        if (!$this->isFiltered) return;

        $this->filters = array_diff($this->filters, ['hide_disliked']);
        $this->isFiltered = false;

        $this->applyAll();
    }

    public function e_sortAZ()
    {
        if ($this->alphabetStreak != 0) return;

        $this->setSort('az');

        $this->timeStreak = 0;
        $this->filterStreak = 0;

        $this->applyAll();
    }

    public function e_sortZA()
    {
        if ($this->alphabetStreak != 0) return;

        $this->setSort('za');

        $this->timeStreak = 0;
        $this->filterStreak = 0;

        $this->applyAll();
    }

    public function e_switchAZ()
    {
        if ($this->alphabetStreak >= 2) return;

        $this->setSort('az');
        $this->alphabetStreak++;

        $this->applyAll();
    }

    public function e_switchZA()
    {
        if ($this->alphabetStreak >= 2) return;

        $this->setSort('za');
        $this->alphabetStreak++;

        $this->applyAll();
    }

    public function e_sortNewest()
    {
        if ($this->timeStreak != 0) return;

        $this->setSort('updated_newest');

        $this->alphabetStreak = 0;
        $this->filterStreak = 0;

        $this->applyAll();
    }

    public function e_sortOldest()
    {
        if ($this->timeStreak != 0) return;

        $this->setSort('updated_oldest');

        $this->alphabetStreak = 0;
        $this->filterStreak = 0;

        $this->applyAll();
    }

    public function e_switchToOldest()
    {
        if ($this->timeStreak >= 2) return;

        $this->setSort('updated_oldest');
        $this->timeStreak++;

        $this->applyAll();
    }

    public function e_switchToNewest()
    {
        if ($this->timeStreak >= 2) return;

        $this->setSort('updated_newest');
        $this->timeStreak++;

        $this->applyAll();
    }

    public function e_sortMostLiked()
    {
        $this->setSort('most_likes');

        $this->timeStreak = 0;
        $this->filterStreak = 0;
        $this->alphabetStreak = 0;

        $this->applyAll();
    }

    public function e_sortDefault()
    {
        $this->filters = array_diff($this->filters, [
            'az','za','updated_oldest','updated_newest','most_likes'
        ]);

        $this->applyAll();
    }

    protected function e_reset()
    {
        $this->isSearchFilled = false;
        $this->isFiltered = false;

        $this->filterStreak = 0;
        $this->alphabetStreak = 0;
        $this->timeStreak = 0;

        $this->currentSort = 'default';
    }

    protected function setSort($type)
    {
        $sorts = ['az','za','updated_oldest','updated_newest','most_likes'];

        $this->filters = array_filter($this->filters, fn($f) => !in_array($f, $sorts));
        $this->filters[] = $type;
    }

    protected function applyAll()
    {
        $cards = $this->cards;

        if (in_array("az", $this->filters)) {
            usort($cards, fn($a,$b) => strcmp($a->name, $b->name));
        } elseif (in_array("za", $this->filters)) {
            usort($cards, fn($a,$b) => strcmp($b->name, $a->name));
        } elseif (in_array("updated_newest", $this->filters)) {
            usort($cards, fn($a,$b) => $b->updated <=> $a->updated);
        } elseif (in_array("updated_oldest", $this->filters)) {
            usort($cards, fn($a,$b) => $a->updated <=> $b->updated);
        } elseif (in_array("most_likes", $this->filters)) {
            usort($cards, fn($a,$b) => $b->likes <=> $a->likes);
        }

        $this->visible = array_values(array_filter($cards, function ($c) {

            if ($this->query && !str_contains($c->name, $this->query)) {
                return false;
            }

            if (in_array("hide_disliked", $this->filters) && $c->disliked) {
                return false;
            }

            return true;
        }));
    }

    public function runInputSequence(array $inputs)
    {
        foreach ($inputs as $input) {
            $method = $this->mapInputToMethod($input);

            if (!method_exists($this, $method)) {
                throw new \Exception("Missing mapping: $input → $method");
            }

            $this->$method();
        }
    }

    protected function mapInputToMethod(string $input): string
    {
        return match ($input) {
            '<reset>' => 'e_reset',
            'search' => 'e_search',
            'hide disliked' => 'e_filter',
            'clear search' => 'e_deleteSearch',
            'show disliked' => 'e_unfilter',

            'sort a to z' => 'e_sortAZ',
            'sort z to a' => 'e_sortZA',
            'switch to a to z' => 'e_switchAZ',
            'switch to z to a' => 'e_switchZA',

            'sort newest' => 'e_sortNewest',
            'sort oldest' => 'e_sortOldest',
            'switch to newest' => 'e_switchToNewest',
            'switch to oldest' => 'e_switchToOldest',

            'sort most liked' => 'e_sortMostLiked',
            'reset sort' => 'e_sortDefault',

            default => throw new \Exception("Unknown input: $input"),
        };
    }

    public function getVisibleNames()
    {
        return array_map(fn($c) => $c->name, $this->visible);
    }
}